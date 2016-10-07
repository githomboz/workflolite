<div class="client-view clearfix">
  <header>
    <a class="cv-logo">
      <img src="#" />
    </a>
  <span class="ref-id">
    Reference ID: 301N38DW0AL
  </span>
  </header>
  <section class="cv-main-body">
    <div class="title">File Progress
      <span class="closing-date">Closing Date: 10/10/2016</span>
    </div>
    <div class="progress-bar">
      <div class="bar" style="width: 19%">19%</div>
    </div>
    <?php for($i = 0; $i <= 20; $i ++) {?>
      <div class="task clearfix">
        <a href="#" class="checkbox checked">
          <i class="fa fa-check"></i>
        </a>
        <span class="name">This is the task that is listed here. It is intentionally long to make sure that long tasks that have a line break still show up properly. I want this to be right even when the task goes onto two or even three lines of copy.</span>
        <span class="description">This is a description of exactly what is happening in this task and how long it can take. This description area can definitely be several lines long as some tasks require more explanation than others. I am filibustering in case you couldn't tell.</span>
      </div>
    <?php } ?>
  </section>
</div><!--/.client-view-->
<script type="text/javascript">
  $(document).ready(function(){
    $(".client-view .task").click(function(){
      var $this = $(this);
      if($this.is('.show-description')) $this.removeClass('show-description'); else $this.addClass('show-description');
    });
  });
</script>
