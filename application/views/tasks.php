<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">

        <h1><i class="fa fa-tasks"></i> Task</h1>
        <div class="tasklist">
            <div class="task-head task">
                <a href="#" class="expander fa fa-minus-square-o"></a>
                <div class="col-title">Open File (<span class="count">14</span>)</div>
                <div class="col-meta">
                    <div class="cols">
                        <div class="col-1 col text-center">Start</div>
                        <div class="col-2 col text-center">End</div>
                        <div class="col-3 col text-left">Comments</div>
                    </div>
                </div>
            </div>

            <?php for($i = 0; $i <= 25; $i ++) include 'widgets/tasklist-task.php' ?>

        </div>
    </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->