<div class="main-mid-section clearfix">
    <div class="main-mid-section-inner clearfix">

        <h1><i class="fa fa-users"></i>Interested Parties</h1>
        <h4>Manage this job's contacts and communication options.</h4>
        <div class="inner-nav-btns">
            <a href="#" class="btn-sync-contacts btn"><i class="fa fa-refresh"></i> Sync Contacts</a>
        </div>

        <span class="last-sync">Last sync with <a href="#">Google Contacts</a>: <span class="datetime">8-24-2016 @ 12:02am</span> </span>
        <input type="text" class="search-contacts" placeholder="Search contacts by name" />
        <div class="people-list">

            <div class="people-form entry clearfix">
                <div class="form-input"><input type="text" name="name" placeholder="Contact Name" /></div>
                <div class="form-input"><input type="text" name="role" placeholder="Role" /></div>
                <div class="form-input icon"><i class="fa fa-envelope"></i> <input type="text" name="email" placeholder="Email" /></div>
                <div class="form-input icon"><i class="fa fa-phone"></i> <input type="text" name="phone" placeholder="Home / Work Phone" /></div>
                <div class="form-input icon"><i class="fa fa-mobile"></i> <input type="text" name="mobile" placeholder="Mobile" /></div>
                <button type="submit" class="btn submit"><i class="fa fa-plus"></i> Add Contact to Job</button>
                <div class="contact-settings clearfix">
                    <span class="setting"><input type="checkbox" /> Email Updates</span>
                    <span class="setting"><input type="checkbox" /> SMS Updates</span>
                </div>
            </div>
            <?php foreach($this->job->getContacts() as $i => $contact){ //var_dump($contact)?>
                <div class="people entry clearfix">
                <div class="head-links">
                    <a href="#edit" class="fa fa-pencil"></a> &nbsp; 
                    <a href="#close" class="fa fa-times"></a>
                </div>
                <div class="role"><?php echo $contact->getValue('role') ?></div>
                <div class="image"><img src="<?php echo base_url() ?>/assets/images/user-avatar.gif" /></div>
                <div class="meta">
                    <span class="data name"><?php echo $contact->getValue('name') ?></span>
                    <span class="data email"><i class="fa fa-envelope"></i> <?php echo $contact->getValue('email') ?></span>
                    <span class="data phone"><i class="fa fa-phone"></i> <?php echo phoneFormat($contact->getValue('phone')) ?></span>
                    <span class="data mobile"><i class="fa fa-mobile"></i> <?php echo phoneFormat($contact->getValue('mobile')) ?></span>
                </div>
                <div class="contact-settings clearfix">
                    <span class="setting"><input type="checkbox" /> Email Updates</span>
                    <span class="setting"><input type="checkbox" /> SMS Updates</span>
                </div>
            </div>
            <?php } ?>
        </div>
    </div><!--/.main-mid-section-inner-->
</div><!--/#main-mid-section-->