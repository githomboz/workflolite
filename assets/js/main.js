var _CS = {
    OrganizationId : null,
    WorkflowId : null,
    JobId : null
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

