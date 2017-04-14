<div class="triggers-popout-main">
  <header>
    <div class="search-filter">
      <select>
        <option>Select One</option>
        <option>Lambda Function</option>
        <option>Custom Form</option>
        <option>Dialog</option>
        <option>HTML Viewer</option>
      </select>
      <input type="text" value="mail" />
      <button class="btn-style submit"><i class="fa fa-search"></i> Search</button>
    </div><!--/.search-filter-->
  </header>
  <div class="body-inner clearfix">
    <div class="panel-1 clearfix">
      <section class="list-column">
        <p class="list-context">Search results for "mail"</p>
        <div class="results-list">
        </div><!--/.results-list-->
      </section>
      <section class="info-box trigger-description">
      </section><!--/.info-box-->
    </div>
    <div class="panel-2 clearfix">
      <h2><span class="type">Lambda</span>: <span class="name">USPS Pickup Request</span></h2>
      <a href="#" class="toggle-payload">Payload Structure</a>
      <div class="viewport activate">
        <header>
          <p>Activate the <span class="trigger-name">{{triggerName}}</span> trigger to access and configure the trigger
            as well as adding a completion test. </p>
        </header>
        <a href="#" class="btn submit btn-activate"><i class="fa fa-power-off"></i> Activate Trigger</a>
      </div><!--/.viewport.payload-->
      <div class="viewport payload">
        <header>
          <h3>Payload Structure</h3>
          <p>Sample structure and setup of the payload structure to be passed to this trigger.</p>
        </header>
        <div class="code">
          <pre><?php echo json_encode([]); ?></pre>
        </div>
      </div><!--/.viewport.payload-->
      <div class="viewport tabs active" data-trigger_id="">
        <ul class="tab-nav clearfix">
          <li class="nav-item" data-tab="config">
            <a href="#"><i class="fa fa-check-square-o"></i> Configuration Options</a>
          </li>
          <li class="nav-item" data-tab="test">
            <a href="#"><i class="fa fa-check-square-o"></i> Task Completion Test</a>
          </li>
          <li class="nav-item admin" data-tab="admin">
            <a href="#"><i class="fa fa-check-square-o"></i> [ Admin Only ]</a>
          </li>
        </ul>
        <div class="tabs-container boxed">
          <div class="tab-content tab-config active" data-tab="config" >
            <div class="data-input-module">
              <header>
                <h4>Configuration Options <span class="help-btn"> [ <a href="#">-help</a> ]</span></h4>
                <span class="input-format"><a href="#">Classic</a> <a href="#" class="active">JSON</a></span>
              </header>
              <div class="help">
                Help Area
              </div>
              <textarea></textarea>
              <footer class="clearfix">
                <span class="validated"><i class="fa fa-check"></i> Validate</span>
                <a href="#" class="save-btn"><i class="fa fa-save"></i> Save</a>
              </footer>
            </div><!--/.data-input-module-->
          </div><!--/.tab-content-->
          <div class="tab-content tab-test" data-tab="test" >
            <div class="data-input-module">
              <header>
                <h4>Task Completion Test <span class="help-btn"> [ <a href="#">-help</a> ]</span></h4>
                <span class="input-format"><a href="#">Classic</a> <a href="#" class="active">JSON</a></span>
              </header>
              <div class="help">
                <p>Set up a completion test to teach the system to self check whether or not a given task is complete.</p>
                <p>Check whether or not meta variables are set, or that they are valid based upon pre-installed
                  custom, or system validation routines.</p>
                <pre>
                  {

                  }
                </pre>
              </div>
              <textarea></textarea>
              <footer class="clearfix">
                <span class="validated"><i class="fa fa-check"></i> Validate</span>
                <a href="#"  class="save-btn"><i class="fa fa-save"></i> Save</a>
              </footer>
            </div><!--/.data-input-module-->
          </div><!--/.tab-content-->
          <div class="tab-content tab-admin" data-tab="admin">
            Admin Fields for updating trigger data
          </div><!--/.tab-content-->
        </div><!--/.tabs-container-->
        <footer>
          <a href="#" class="btn-deactivate"><i class="fa fa-power-off"></i> Deactivate</a>
          <button type="submit" class="btn-style submit"><i class="fa fa-save"></i> Save Changes</button>
        </footer>
      </div><!--/.viewport.tabs-->

    </div><!--panel-2-->
  </div>
</div><!--/.triggers-popout-main-->