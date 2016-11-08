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

var isNotesPage = false;
function _isNotePage(){
    if(!isNotesPage){
        isNotesPage = $(".job-notes-page").length >= 1;
    }
    return isNotesPage;
}
