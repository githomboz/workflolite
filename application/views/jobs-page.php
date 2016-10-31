<div class="main-mid-section clearfix">
  <div class="main-mid-section-inner clearfix">

    <h1><i class="fa "></i>Jobs Queue</h1>
    <h4>List of your current jobs and their status relative to completion.</h4>

    <div class="inner-nav-btns">
      <a href="#" class="btn"><i class="fa fa-plus"></i> Create a Job</a>
    </div>

    <div class="jobs-list">
      <div class="job-entry clearfix">
        <div class="job-title">
          <h2>This is the job name</h2>
          <span>Completed 27/30</span>
        </div>
        <div class="job-tasks clearfix">
          <?php for($i = 0; $i <= 30; $i ++) { ?>
          <div class="job-task <?php echo $i == 27 ? 'active' : '' ?>">
            <div class="status"><span class="content">Completed</span></div>
            <div class="job-inner">
              <div class="dates content">
                <span class="start-date">9-25-2016</span>
                <span class="completion-date">N/A</span>
              </div>
              <h2 class="content">This is the name of a specific task</h2>
              <div class="task-details content">
                <a href="#assignee" class="assignee">Deanna Courtney</a>
                <span class="estimated-time">~3.5hrs</span>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>

  </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
