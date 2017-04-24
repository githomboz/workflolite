<div class="binded-trigger-box-overlay">
  <div class="binded-trigger-box stand-alone boxed clearfix">
    <header class="inner-head clearfix">
      <div class="thumb sa">

      </div>
      <div class="titles">
        <h2></h2>
        <h3></h3>
      </div>
      <div class="upper-settings">
        <span class="deadline-txt">Complete by <span class="date"></span> </span>
        <i class="fa fa-cog"></i>
      </div>
      <div class="lower-settings">
        <span class="task-count-txt">Project Task #<span class="task-num"></span> of <span class="task-count"></span></span>
        <span class="time-tracker-btn"><i class="fa fa-pause-circle"></i> 00:00:00</span>
      </div>
    </header>
    <div class="mid-section clearfix">
      <nav class="tabbed-nav clearfix">
        <ul>
          <li class="item tasks-nav selected"><a href="#" class="fa fa-check-square" rel="tasks"></a> </li>
          <li class="item database-nav"><span class="num-flag red"></span> <a href="#" class="fa fa-database" rel="metadata"></a> </li>
          <li class="contacts-nav"><a href="#" class="fa fa-users" rel="contacts"></a> </li>
          <li class="notes-nav"><a href="#" class="fa fa-comment" rel="notes"></a> </li>
          <li class="time-nav"><a href="#" class="fa fa-clock-o" rel="time"></a> </li>
          <li class="stats-nav"><a href="#" class="fa fa-bar-chart" rel="stats"></a> </li>
        </ul>
      </nav>
      <div class="tabbed-content-container clearfix">
        <section class="tabbed-content tasks clearfix show" data-status="completed" data-slide="tasks">
          <span class="status-info">Status: <span class="status"></span></span>
          <div class="task-trigger">
            <div class="trigger-type"><span class="trigger-type-name"></span> | <span class="trigger-type-desc"></span></div>
            <h1>#<span class="num"></span>) <span class="group"></span>: <span class="icon"></span> <span class="name"></span></h1>
            <div class="description"></div>
            <div class="instructions"></div>
            <div class="dynamic-content" data-task_template_id=""></div>
            <div class="bottom-links">
              <a href="#" class="completion-test-btn"><i class="fa fa-heartbeat"></i><span class="info-data"> Run completion tests</span></a>
              <span class="ajax-response success">[ <i class="fa fa-check"></i> Success | <a href="#" class="completion-test-report-btn">Report</a> ]</span>
            </div>
          </div><!--/task-trigger-->

          <div class="task-data-block">
            <a href="#"><i class="fa fa-search"></i> Toggle Raw</a>
            <pre></pre>
          </div><!--/task-data-block-->
        </section>
        <section class="tabbed-content metadata clearfix" data-slide="metadata">
          <h1>Job Metadata</h1>
          <div class="column-list">
            <div class="meta-fields">
              <div class="head-links clearfix">
                <a href="#" class="key key-sort-btn">Name / Key &nbsp; <i class="fa fa-caret-down"></i></a>
                <a href="#" class="value value-sort-btn">Value &nbsp; <i class="fa fa-caret-down"></i></a>
              </div>
              <div class="entries clearfix">
              </div>
            </div><!--/.meta-fields-->
            <form class="tab-form clearfix">
              <input type="text" placeholder="Enter new meta key" />
              <select>
                <option>Select Type</option>
                <option>String</option>
                <option>URL</option>
                <option>Date</option>
                <option>Number</option>
                <option>Boolean</option>
                <option>Phone</option>
                <option>Text</option>
                <option>Array</option>
                <option>Address</option>
                <option>Twitter Handle</option>
              </select>
              <button type="submit"><i class="fa fa-plus"></i> Add Key</button>
            </form>
          </div><!--/.column-list-->
          <div class="column-details">
            <div class="inner-details">
              <h2>Closing Date</h2>
              <ul class="meta-meta">
                <li class="meta-entry slug show">
                  <span class="meta-key">Slug: </span>
                  <span class="meta-value"><span class="val"></span> <a href="#" class="fa fa-info-circle"></a></span>
                </li>
                <li class="meta-entry type show">
                  <span class="meta-key">Type: </span>
                  <span class="meta-value"><span class="val"></span> <a href="#" class="fa fa-info-circle"></a></span>
                </li>
                <li class="meta-entry format">
                  <span class="meta-key">Format: </span>
                  <span class="meta-value"><span class="val"></span> <a href="#" class="fa fa-info-circle"></a></span>
                </li>
                <li class="meta-entry formatted">
                  <span class="meta-key">Formatted: </span>
                  <span class="meta-value"><span class="val"></span></span>
                </li>
                <li class="meta-entry value show">
                  <span class="meta-key">Value: </span>
                  <span class="meta-value"><span class="val"></span> <a href="#" class="fa fa-pencil"></a> </span>
                </li>
              </ul>
              <form>
                <button class="btn-style btn submit" type="submit" disabled="disabled">Submit</button>
              </form>
            </div><!--/.inner-details-->
          </div><!--/.column-details-->
        </section>
      </div><!--/.tabbed-content-container-->
    </div><!--/.mid-section-->
    <div class="action-btns clearfix">
      <button class="prev-task"><i class="fa fa-fast-backward"></i>&nbsp; Prev. Task</button>
      <button class="next-task"><i class="fa fa-fast-forward"></i>&nbsp; Next Task</button>
      <button class="mark-complete inverse"><i class="fa fa-check"></i>&nbsp; Mark Complete</button>
    </div>
  </div><!--/.binded-trigger-box-->
</div><!--/.binded-trigger-box-overlay-->