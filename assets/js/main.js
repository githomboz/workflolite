var _CS = {
    OrganizationId : null,
    WorkflowId : null,
    JobId : null,
    UserId : null
};

function _CS_Get_Organization_ID(){
    if(_CS.OrganizationId) return _CS.OrganizationId;
    _CS.OrganizationId = $("body").data('organization');
    return _CS.OrganizationId;
}

function _CS_Get_Workflow_ID(){
    if(_CS.WorkflowId) return _CS.WorkflowId;
    _CS.WorkflowId = $("body").data('workflow');
    return _CS.WorkflowId;
}

function _CS_Get_Project_ID(){
    if(_CS.ProjectId) return _CS.ProjectId;
    _CS.ProjectId = $("body").data('entity');
    return _CS.ProjectId;
}

function _CS_Get_Entity_ID() {
    if(_CS_Get_Project_ID() != '') return _CS_Get_Project_ID();
    return _CS_Get_Job_ID();
}

function _CS_Get_Template_ID(){
    if(_CS.TemplateId) return _CS.TemplateId;
    _CS.TemplateId = $("body").data('template');
    return _CS.TemplateId;
}

function _CS_Get_Template_Version(){
    if(_CS.TemplateVersion) return _CS.TemplateVersion;
    _CS.TemplateVersion = $("body").data('version');
    return _CS.TemplateVersion;
}

function _CS_Get_Job_ID(){
    if(_CS.JobId) return _CS.JobId;
    _CS.JobId = $("body").data('job');
    return _CS.JobId;
}

function _CS_Get_User_ID(){
    if(_CS.UserId) return _CS.UserId;
    _CS.UserId = $("body").data('user');
    return _CS.UserId;
}

function _CS_Get_Entity(){
    if(_CS.EntityType) return _CS.EntityType;
    _CS.EntityType = $("body").data('entity_type');
    return _CS.EntityType;
}


var isNotesPage = false;
function _isNotePage(){
    if(!isNotesPage){
        isNotesPage = $(".job-notes-page").length >= 1;
    }
    return isNotesPage;
}

function get50States(){
    return {
        "AL": "Alabama",
        "AK": "Alaska",
        //"AS": "American Samoa",
        "AZ": "Arizona",
        "AR": "Arkansas",
        "CA": "California",
        "CO": "Colorado",
        "CT": "Connecticut",
        "DE": "Delaware",
        "DC": "District Of Columbia",
        //"FM": "Federated States Of Micronesia",
        "FL": "Florida",
        "GA": "Georgia",
        "GU": "Guam",
        "HI": "Hawaii",
        "ID": "Idaho",
        "IL": "Illinois",
        "IN": "Indiana",
        "IA": "Iowa",
        "KS": "Kansas",
        "KY": "Kentucky",
        "LA": "Louisiana",
        "ME": "Maine",
        "MH": "Marshall Islands",
        "MD": "Maryland",
        "MA": "Massachusetts",
        "MI": "Michigan",
        "MN": "Minnesota",
        "MS": "Mississippi",
        "MO": "Missouri",
        "MT": "Montana",
        "NE": "Nebraska",
        "NV": "Nevada",
        "NH": "New Hampshire",
        "NJ": "New Jersey",
        "NM": "New Mexico",
        "NY": "New York",
        "NC": "North Carolina",
        "ND": "North Dakota",
        //"MP": "Northern Mariana Islands",
        "OH": "Ohio",
        "OK": "Oklahoma",
        "OR": "Oregon",
        "PW": "Palau",
        "PA": "Pennsylvania",
        "PR": "Puerto Rico",
        "RI": "Rhode Island",
        "SC": "South Carolina",
        "SD": "South Dakota",
        "TN": "Tennessee",
        "TX": "Texas",
        "UT": "Utah",
        "VT": "Vermont",
        "VI": "Virgin Islands",
        "VA": "Virginia",
        "WA": "Washington",
        "WV": "West Virginia",
        "WI": "Wisconsin",
        "WY": "Wyoming"
    };
}

// Setup Shortcodes
if(typeof WFShortcodeLib != 'undefined') {
    WFShortcodeLib.registerTag('youtube', function(options, contents){
        var output = '';
        if(typeof options.action != 'undefined') {
            switch (options.action){
                case 'show-video':
                    if( typeof options.videoId ) {
                        output += '<iframe class="youtube-iframe" width="100%" height=350" src="https://www.youtube.com/embed/' + options.videoId + '" frameborder="0" allowfullscreen></iframe>';
                    } else console.error('Must provide a valid video id');
                    break;
            }
        }
        return output;
    });

    WFShortcodeLib.registerTag('metadata', function(options, contents){
        var output = '';
        if(typeof options.action != 'undefined') {
            switch (options.action){
                case 'get-value':
                    break;
            }
        }
        return output;
    });

    WFShortcodeLib.registerTag('image', function(options, contents){
        var output = '', srcSet = false;
        if( typeof options.source != 'undefined') {
            options.action = 'show-image';
            srcSet = true;
        }

        if(typeof options.action != 'undefined') {
            switch (options.action){
                case 'show-image':
                    if( srcSet || typeof options.source != 'undefined' ) {
                        output += '<img src="' + options.source + '" ';
                        if( options.class ) output += 'class="' + + '" ';
                        if( options.width ) output += 'width="' + + '" ';
                        if( options.height ) output += 'height="' + + '" ';
                        output += '/>';
                    } else console.error('Must provide a valid video id');
                    break;
            }
        }
        return output;
    });

    WFShortcodeLib.registerTag('link', function(options, contents){
        var output = '';
        if( typeof options.href == 'undefined') console.error('Must provide a valid href');
        else {
            output += '<a ';
            for ( var att in options ) {
                output += att + '="' + options[att] + '" ';
            }
            output += '>' + contents + '</a>';
        }
        return output;
    });

    var WFChecklistInstance = 0;
    var WFChecklistActive = false;

    var WFChecklist = function(options, instance){

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

        if(!instance) {
            WFChecklistInstance ++;
            instance = WFChecklistInstance;
        }

        this.instance               = instance;
        this.$checklist             = $(".checklist-container[data-instance=" + this.instance + "]");
        this.options                = defaults;
        this.output                 = '';
        this.steps                  = [];
        this.completePercent        = 0; // Percentage of steps complete
        this.completePercentRD      = 0; // Percentage of steps complete (rounded up)
        this.completeCount          = 0;
    };

    WFChecklist.prototype.update = function(){
        this.output = '';
        this.completeCount = 0;
        // Loop through steps and generate percentage
        for ( var i in this.steps ){
            if( this.steps[i].completed ) this.completeCount ++;
        }

        // Update percentage
        if(this.steps.length > 0) this.completePercent = this.completeCount / this.steps.length;
        this.completePercentRD = Math.round(this.completePercent);
        this.activate();
        this.render();
    };

    WFChecklist.prototype.activate = function(){
        if(!WFChecklistActive) {
            WFChecklistActive = true;
            //$(document).on('click', '.checklist-container .checklist-entry a', this.handleListClick.bind(this));
            $(document).on('click', '.checklist-container .checklist-entry a, .checklist-container .checklist-entry .fa', this.handleListClick);
        }
    };

    WFChecklist.prototype.render = function(){
        if(!this.$checklist.length) this.$checklist = $(".checklist-container[data-instance=" + this.instance + "]");
        if(this.$checklist.length >= 1) {
            this.$checklist.attr('data-steps', JSON.stringify(this.steps));
            this.$checklist.attr('data-options', JSON.stringify(this.options));
            this.$checklist.html(this.html(false));
        }
    };

    WFChecklist.prototype.handleListClick = function(e){
        e.preventDefault();
        var $el = $(this),
            $li = $el.parents('li'),
            step = parseInt($li.attr('data-step')),
            completed = $li.is('.completed'),
            $checklist = $li.parents('.checklist-container'),
            checklist = GetWFChecklistByElement($checklist);

        if(completed) checklist.markStepIncomplete(step); else checklist.markStepComplete(step);
        console.log(e.target, $(this), this, 'step ' + step , completed, $checklist, checklist);
        return false;
    };

    WFChecklist.prototype.html = function(includeWrapper){
        var idText = this.options.id ? ' id="' + this.options.id + '" ' :'';
        if(includeWrapper) this.output += '<div class="checklist-container"' + idText + ' data-options=\'' + JSON.stringify(this.options) + '\' data-instance="' + this.instance + '" data-steps=\'' + JSON.stringify(this.steps) + '\'>';
        if(this.options.showTitle && this.options.title) this.output += this.drawTitle();
        if(this.options.showCount) this.output += this.drawCount();
        if(this.options.showBar) this.output += this.drawProgressBar();
        this.output += this.drawChecklist();
        if(includeWrapper) this.output += '</div><!--/.checklist-container-->';
        return this.output;
    };

    WFChecklist.prototype.drawProgressBar = function(){
        var percentage = (this.completePercent * 100),
            output = '<div class="progress-bar-container">';
        output += '<div class="bar" style="width: ' + percentage + '%"><span class="percentage">' + percentage + '%</span></div>';
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
    };

    WFChecklist.prototype.removeStep = function(stepNum){

        this.update();
    };

    WFChecklist.prototype.markStepComplete = function(stepNum){
        var index = stepNum - 1;
        if(typeof this.steps[index] != 'undefined') {
            this.steps[index].completed = true;
            this.update();
        }
        return this;
    };

    WFChecklist.prototype.markStepIncomplete = function(stepNum){
        var index = stepNum - 1;
        if(typeof this.steps[index] != 'undefined') {
            this.steps[index].completed = false;
            this.update();
        }
        return this;
    };

    function GetWFChecklistById(id){
        id = id.replace('#','');
        var $checklist = $("#"+id+".checklist-container");
        return GetWFChecklistByElement($checklist);
    }

    function GetWFChecklistByElement($checklist){
        var instance = parseInt($checklist.attr('data-instance')),
            steps = JSON.parse($checklist.attr('data-steps')),
            options = JSON.parse($checklist.attr('data-options')),
            checklist = new WFChecklist(options, instance)
            ;

        checklist.steps = steps;
        console.log(instance, steps, options, checklist);
        return checklist;
    }

    WFShortcodeLib.registerTag('checklist', function(options, contents){
        var delimiter = '||',
            rawSteps,
            checklist = new WFChecklist(options);
        contents = contents ? contents.trim() : '';

        rawSteps = contents.split(delimiter);
        for ( var i in rawSteps ) checklist.addStep({ title : rawSteps[i] } );

        console.log(checklist);
        checklist.activate();
        return checklist.html(true);
    });

}