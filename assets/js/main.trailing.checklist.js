/**
 * Created by benezerlancelot on 8/9/17.
 */

var WFChecklistActive           = false;
var WFChecklistClass            = ".checklist-container";

function WFChecklistGenerateId(title){
    return md5('task' + BindedBox.task().data.sortOrder + 'Checklist' + title);
}


var WFChecklist = function(options, id){

    var defaults = {
        showBar                 : true,
        showBarPercentage       : true,
        showCount               : true,
        showTitle               : true
    };

    if(options){
        for ( var key in options ) {
            defaults[key] = options[key];
        }
    }

    if(!id) {
        id = WFChecklistGenerateId(defaults.title);
    }

    this.className              = WFChecklistClass;
    this.loadedFromStore        = false;
    this.id                     = id;
    this.slug                   = 'task' + BindedBox.task().data.sortOrder + 'Checklist_' + this.id;
    this.$checklist             = $(this.className + "[data-id=" + this.id + "]");
    this.taskNum                = BindedBox.task().data.sortOrder;
    this.options                = defaults;
    this.output                 = '';
    this.completePercent        = 0; // Percentage of steps complete
    this.completePercentRD      = 0; // Percentage of steps complete (rounded up)
    this.completeCount          = 0;

    //console.log('Checklist ' + this.id + ' initialized');
    if(typeof _METADATA[this.slug] != 'undefined'){
        //console.log(this.slug, _METADATA[this.slug]);
        var _metadata = JSON.stringify(_METADATA[this.slug]);
        _metadata = JSON.parse(_metadata);
        for ( var i in _metadata.value.steps ) {
            _metadata.value.steps[i].completed = _metadata.value.steps[i].completed === 'true';
        }
        this.steps = _metadata.value.steps;
        this.loadedFromStore = true;
        //console.log('Loaded from Metadata', this.steps);
        //console.log(_METADATA);
        this.update();
    } else {
        this.steps                  = [];
        //console.log('No steps loaded yet. Waiting for new steps', this.steps);
    }
};

WFChecklist.prototype.update = function(){
    this.output = '';
    this.completeCount = 0;
    // Loop through steps and generate percentage
    for ( var i in this.steps ){
        if( this.steps[i].completed ) this.completeCount ++;
    }

    // Update percentage
    if(this.steps.length > 0) this.completePercent = (this.completeCount / this.steps.length) * 100;
    this.completePercentRD = Math.round(this.completePercent);
    this.activate().render();
    return this;
};

WFChecklist.prototype.activate = function(){
    if(!WFChecklistActive) {
        //console.log('activating this here');
        WFChecklistActive = true;
        $(document).on('click', this.className + ' .checklist-entry a, ' + this.className + ' .checklist-entry .fa', this.handleListClick);
    }
    return this;
};

WFChecklist.prototype.persist = function(){
    var that = this;
    CS_API.call('ajax/update_checklist',
        function(){
            // beforeSend
            //BindedBox.disableTraffic();
        },
        function(data){
            // success
            if(data.errors == false && data.response.success){
                SlideTasks.validateAndApplyUpdates(data, true);
                //BindedBox.enableTraffic();
            } else {
                //BindedBox.enableTraffic();
                if(data.errors && typeof data.errors[0] != 'undefined') alertify.error(data.errors[0]);
            }
        },
        function(){
            // error
            //BindedBox.enableTraffic();
            alertify.error('Error', 'An error has occurred.');
        },
        {
            projectId: _CS_Get_Entity_ID(),
            taskId : BindedBox.task().id,
            sortOrder : BindedBox.task().data.sortOrder,
            checklist : {
                steps : that.steps,
                options : that.options,
                id : that.id
            }
        },
        {
            method: 'POST',
            preferCache : false
        }
    );
};

WFChecklist.prototype.render = function(){
    this.$checklist = $(this.className + "[data-id=" + this.id + "]");
    if(this.$checklist.length >= 1) {
        this.$checklist.attr('data-task_num', this.taskNum);
        this.$checklist.attr('data-steps', JSON.stringify(this.steps));
        this.$checklist.attr('data-options', JSON.stringify(this.options));
        this.$checklist.html(this.html(false));
        //console.log('showing checklist', this.steps);
    } else {
        //console.log('not showing checklist', this.steps);
    }
};

WFChecklist.prototype.handleListClick = function(e){
    e.preventDefault();
    var $el = $(this),
        $li = $el.parents('li'),
        step = parseInt($li.attr('data-step')),
        completed = $li.is('.completed'),
        $checklist = $li.parents( WFChecklistClass );

    //console.log('==== CLICK CAPTURED ====');
    //console.log(e.target, $(this), this, 'step ' + step , completed, $checklist);
    var checklist = GetWFChecklistByElement($checklist);
    //console.log(checklist);


    if(checklist){
        if(completed) checklist.markStepIncomplete(step).persist(); else checklist.markStepComplete(step).persist();
    }
    return false;
};

WFChecklist.prototype.html = function(includeWrapper){
    if(includeWrapper) {
        this.output += '<div class="checklist-container" id="' + this.slug;
        this.output += '" data-options=\'' + JSON.stringify(this.options) + '\' ';
        this.output += 'data-id="' + this.id + '" ';
        this.output += 'data-task_num="' + this.taskNum + '" ';
        this.output += 'data-steps=\'' + JSON.stringify(this.steps) + '\'';
        this.output += '>';
    }
    if(this.options.showTitle && this.options.title) this.output += this.drawTitle();
    if(this.options.showCount) this.output += this.drawCount();
    if(this.options.showBar) this.output += this.drawProgressBar();
    this.output += this.drawChecklist();
    if(includeWrapper) this.output += '</div><!--/.checklist-container-->';
    return this.output;
};

WFChecklist.prototype.drawProgressBar = function(){
    var output = '<div class="progress-bar-container">';
    output += '<div class="bar" style="width: ' + this.completePercentRD + '%"><span class="percentage">' + this.completePercentRD + '%</span></div>';
    output += '</div><!--/.progress-bar-->';
    return output;
};

WFChecklist.prototype.drawTitle = function(){
    return '<h2><i class="fa fa-list-ul"></i> Checklist: <span class="title">' + this.options.title + '</span></h2>'
};

WFChecklist.prototype.drawCount = function(){
    var output = '<span class="checklist-counts"><span class="steps-complete-count">' +  this.completeCount  + '</span>/<span class="steps-count">' + this.steps.length + '</span> Complete</span>';
    return output;
};

WFChecklist.prototype.drawChecklist = function(){
    var output = '<ul>';
    for ( var i in this.steps ) {
        output += '<li class="checklist-entry' + ( this.steps[i].completed ? ' completed':'') + '" data-step="' + (parseInt(i) + 1) + '">';
        output += '<span class="checkbox-icon">' + ( this.steps[i].completed ? '<i class="fa fa-check-square-o"></i>':'<i class="fa fa-square-o"></i>') + '</span> ';
        output += '<a href="#">' + this.steps[i].title + '</a>';
        output += '</li>';
    }

    output += '</ul>';
    return output;
};

WFChecklist.prototype.addStep = function(step){
    if(typeof step.title != 'undefined'){
        if(typeof step.completed == 'undefined') step.completed = false;
        this.steps.push(step);
        this.update();
    } else {
        console.error('Error;  Adding step with no title');
    }
    return this;
};

WFChecklist.prototype.removeStep = function(stepNum){

    this.update();
    return this;
};

WFChecklist.prototype.markStepComplete = function(stepNum){
    var index = stepNum - 1;
    if(typeof this.steps[index] != 'undefined') {
        this.steps[index].completed = true;
        this.update();
            //.persist();
    }
    return this;
};

WFChecklist.prototype.markStepIncomplete = function(stepNum){
    var index = stepNum - 1;
    if(typeof this.steps[index] != 'undefined') {
        this.steps[index].completed = false;
        this.update();
            //.persist();
    }
    return this;
};

function GetWFChecklistById(id){
    id = id.replace('#','');
    var selector = "#"+id+".checklist-container", $checklist = $(selector);
    //console.log(selector, $checklist);
    return GetWFChecklistByElement($checklist);
}

function GetWFChecklistByElement($checklist){
    //console.log($checklist, typeof $checklist);
    var optionsJSON = $checklist.attr('data-options');
    var stepsJSON = $checklist.attr('data-steps');
    if(optionsJSON){
        var id = $checklist.attr('data-id'),
            steps = JSON.parse(stepsJSON),
            options = JSON.parse(optionsJSON),
            checklist = new WFChecklist(options, id)
            ;

        checklist.steps = steps;
        //console.log(id, options, checklist);
        return checklist;
    }
}

WFShortcodeLib.registerTag('checklist', function(options, contents){
    var id = WFChecklistGenerateId(options.title),
        checklist = new WFChecklist(options, id);

    //console.log(checklist, options, contents);

    if(!checklist.loadedFromStore) {
        var delimiter = '||',
            rawSteps;
        contents = contents ? contents.trim() : '';
        rawSteps = contents.split(delimiter);
        for ( var i in rawSteps ) checklist.addStep({ title : rawSteps[i] } );
        checklist.update();
    }

    return checklist.html(true);
});

function WFChecklistRedraw(){
    // Find all checklists in dom
    var $checklists = $(WFChecklistClass);
    //if($checklists.length == 0) console.log('==== CHECKLISTS REDRAW IGNORED ===='); else console.log('==== ' + $checklists.length + ' CHECKLISTS FOUND. INITIALIZING ====');
    $checklists.each(function(index, item){
        var $checklist = $(item),
            id = $checklist.attr('id'),
            shortcode_init = $checklist.attr('data-shortcode_init'),
            checklist = GetWFChecklistById(id);

        //console.log(id, index, item, checklist, _METADATA, shortcode_init);
        //GetWFChecklistById(id).render();

        // Get the checklist's id
        // Loop through and re-render each checklist if a metadata match is found
    });
}

PubSub.subscribe('shortCodes.appliedToHTML', WFChecklistRedraw);