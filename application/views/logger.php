<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">

        <h1><i class="fa fa-history"></i> WorkFlo Logger</h1>
        <div class="wflogger-entries">

        </div>
        <?php
        CI()->load->library('pagination');

        $queryDefaults = WFLogger::QueryDefaults();
        $args = array_merge($queryDefaults, (array) CI()->input->get());
        $config['base_url'] = (strpos(current_url(), ':8888') >= 0 ? '/source':''). '/admin/logger?';
        $config['total_rows'] = WFLogger::Read($args, true);
        $config['per_page'] = $args['limit'];
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
        color: rgba(0, 0, 0, .6);
    }

    .wflogger-entries .entry .type {
        padding-right: 20px;
        text-transform: uppercase;
        color: rgba(0, 0, 0, .6);
    }

    .wflogger-entries .entry .type.errors {
        color: rgba(184, 5, 0, 0.55);
    }

    .wflogger-entries .entry .message {
        color: #5e72af;
        display: block;
    }

    .wflogger-entries .entry.type-errors .message {
        color: rgba(184, 5, 0, 1);
    }

    .wflogger-entries .entry .data {
        color: #555;
        display: block;
    }

    .wflogger-entries .entry .data .btn-json-format {
        color: #006600;

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