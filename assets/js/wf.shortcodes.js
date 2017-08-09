/*
 * wf.shortcodes.js 1.1.0
 * By Benezer Jahdy Lancelot
 * Based on shortcode.js by @nicinabox // https://github.com/nicinabox/shortcode.js/issues
 * License: MIT
 */

var WFShortcodeLib = (function(){

    var _registeredTags = {};
    var _regex = '\\[{name}(.*?)?\\](?:([\\s\\S]*?)(\\[\/{name}\\]))?';

    function _registerTag(tag, callback){
        _registeredTags[tag] = callback;
    }

    function _registerTags(callbacks){
        for ( var tag in callbacks ) _registerTag(tag, callbacks[tag]);
    }

    function _getRegisteredTags(){
        return _registeredTags;
    }

    function _getRegex(){
        return _regex;
    }

    return {
        registerTag         : _registerTag,
        registerTags        : _registerTags,
        getTags             : _getRegisteredTags,
        getRegex            : _getRegex
    };
})();


var WFShortcode = function(input) {
    //if (!el) { return; }

    this.matchesProcessed   = false;
    this.input              = input;
    this.output             = input;
    this.matches            = [];

    this.matchTags();
    if( this.matches.length > 0 && !this.matchesProcessed ) this.processTags();
    return this;
};

WFShortcode.prototype.matchTags = function() {
    var input = this.input, _input, instances, tags = WFShortcodeLib.getTags(),
        match, re, contents, regex, tag, options;

    for (var key in tags) {
        //console.log(key);
        if (!tags.hasOwnProperty(key)) { return; }
        re        = this.template(WFShortcodeLib.getRegex(), { name: key });
        instances = input.match(new RegExp(re, 'g')) || [];

        for (var i = 0, len = instances.length; i < len; i++) {
            match = instances[i].match(new RegExp(re));
            contents = match[3] ? '' : undefined;
            tag      = match[0];
            regex    = this.escapeTagRegExp(tag);
            options  = this.parseOptions(match[1]);
            _input    = match[0];

            if (match[2]) {
                contents = match[2].trim();
                tag      = tag.replace(contents, '');
                regex    = regex.replace(contents, '([\\s\\S]*?)');
            }

            this.matches.push({
                name: key,
                input: _input,
                tag: tag,
                regex: regex,
                options: options,
                contents: contents
            });
        }
    }
};

WFShortcode.prototype.processTags = function() {
    var tags = WFShortcodeLib.getTags(), val;
    for ( var i in this.matches ){
        // Make sure is callable
        if(typeof tags[this.matches[i].name] == 'function') {
            val = tags[this.matches[i].name](this.matches[i].options, this.matches[i].contents);
            this.output = this.output.replace(this.matches[i].input, val);
        }
    }
    this.matchesProcessed = true;
};

WFShortcode.prototype.parseOptions = function(stringOptions) {
    var options = {}, _set;
    if (!stringOptions) { return; }

    _set = stringOptions
        .replace(/(\w+=)/g, '\n$1')
        .split('\n');
    _set.shift();

    for (var i = 0; i < _set.length; i++) {
        var kv = _set[i].split('=');
        options[kv[0]] = kv[1].replace(/\'|\"/g, '').trim();
    }

    return options;
};

WFShortcode.prototype.escapeTagRegExp = function(regex) {
    return regex.replace(/[\[\]\/]/g, '\\$&');
};

WFShortcode.prototype.template = function(s, d) {
    for (var p in d) {
        s = s.replace(new RegExp('{' + p + '}','g'), d[p]);
    }
    return s;
};

// Polyfill .trim()
String.prototype.trim = String.prototype.trim || function () {
    return this.replace(/^\s+|\s+$/g, '');
};

function WFPRocessShortcodes(input){
    var wfsc = new WFShortcode(input);
    //console.log(input, wfsc);
    return wfsc.output;
}