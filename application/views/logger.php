<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">

        <h1><i class="fa fa-history"></i> WorkFlo Logger</h1>
        <div class="wflogger-entries">

        </div>
        <?php
        CI()->load->library('pagination');

        $config['base_url'] = (strpos(current_url(), ':8888') >= 0 ? '/source':''). '/admin/logger?';
        $config['total_rows'] = 200;
        $config['per_page'] = 20;
        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';

        CI()->pagination->initialize($config);

        echo CI()->pagination->create_links();
        ?>
    </div>
</div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->
<style type="text/css">
    .wflogger-entries {
        line-height: 1.5em;
        font-size: 14px;
    }
    .wflogger-entries .entry {
        margin-bottom: .5em;
    }

    .wflogger-entries .entry .dateAdded {
        padding-right: 20px;
    }

    .wflogger-entries .entry .type {
        padding-right: 20px;
        text-transform: uppercase;
    }

    .wflogger-entries .entry .message {
        color: #5e72af;
        display: block;
    }

    .wflogger-entries .entry .data {
        color: #555;
        display: block;
    }

    .wflogger-entries .entry .context {
        color: rgba(115, 115, 115, 0.64);
        font-size: 12px;
        display: block;
    }



</style>
<script type="text/javascript">
    $(document).ready(function(){
        CS_WFLogger.setQuery(JSON.parse('<?php echo json_encode((array) $this->input->get()) ?>'));
    });
</script>