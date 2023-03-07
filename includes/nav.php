<?php

use Classes\{Session};
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light" role="navigation">
    <a href="<?=SROOT?>" class="navbar-brand">
        <?=SITE_NAME?>
    </a>
    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navigationMenu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navigationMenu">
        <ul class="navbar-nav">
            <li class="nav-item <?= (get_page_name() == 'index') ? 'active' : '' ?>">
                <a href="<?=SROOT?>" class="nav-link"><i class="fas fa-home" aria-hidden="true"></i> Home</a>
            </li>

            <li class="nav-item">
                <a href="<?=SROOT?>#trends" class="nav-link"><i class="fas fa-fire-alt" aria-hidden="true"></i> Trending stories!</a>
            </li>

            <li class="nav-item dropdown <?= (get_page_name() == 'category') ? 'active' : '' ?>">
                <a href="#" class="nav-link dropdown-toggle" id="navCategories" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fas fa-chart-pie" aria-hidden="true"></i> Categories</a>
                <div class="dropdown-menu" role="menu" aria-labelledby="navCategories">
                    <a href="<?=SROOT?>category.php?type=business" class="dropdown-item">Business</a>
                    <a href="<?=SROOT?>category.php?type=sports" class="dropdown-item">Sports</a>
                    <a href="<?=SROOT?>category.php?type=entertainment" class="dropdown-item">Entertainment</a>
                    <a href="<?=SROOT?>category.php?type=politics" class="dropdown-item">Politics</a>
                    <a href="<?=SROOT?>category.php?type=health" class="dropdown-item">Health</a>
                    <a href="<?=SROOT?>category.php?type=general" class="dropdown-item">General</a>
                    <a href="<?=SROOT?>category.php?type=social" class="dropdown-item">Social</a>
                    <a href="<?=SROOT?>category.php?type=crime" class="dropdown-item">Crime</a>
                    <a href="<?=SROOT?>category.php?type=legal" class="dropdown-item">Legal</a>
                    <a href="<?=SROOT?>category.php?type=lifestyle" class="dropdown-item">Lifestyle</a>
                    <a href="<?=SROOT?>category.php?type=international" class="dropdown-item">International</a>
                    <a href="<?=SROOT?>category.php?type=art" class="dropdown-item">Art</a>
                    <a href="<?=SROOT?>category.php?type=tech" class="dropdown-item">Tech</a>
                    <a href="<?=SROOT?>category.php?type=education" class="dropdown-item">Education</a>
                    <a href="<?=SROOT?>category.php?type=auto" class="dropdown-item">Auto</a>
                    <a href="<?=SROOT?>category.php?type=national" class="dropdown-item">National</a>
                </div>
            </li>

            <?php if(Session::exists('uid')): ?>
                <li class="nav-item <?= (get_page_name() == 'dashboard') ? 'active' : '' ?>">
                    <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard</a>
                </li>

                <li class="nav-item <?= (get_page_name() == 'create') ? 'active' : '' ?>">
                    <a href="create.php" class="nav-link"><i class="fas fa-plus-circle" aria-hidden="true"></i> Create</a>
                </li>

                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navHistory" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fas fa-history" aria-hidden="true"></i> History</a>
                    <div class="dropdown-menu" role="menu" aria-labelledby="navHistory">
                        <a href="<?=SROOT?>activity-history.php" class="dropdown-item <?= (get_page_name() == 'activity-history') ? 'active' : '' ?>">Activity history</a>
                        <a href="<?=SROOT?>account-history.php" class="dropdown-item <?= (get_page_name() == 'account-history') ? 'active' : '' ?>">Account history</a>
                        <a href="<?=SROOT?>subscription-history.php" class="dropdown-item <?= (get_page_name() == 'subscription-history') ? 'active' : '' ?>">Subscription history</a>
                    </div>
                </li>

                <li class="nav-item <?= (get_page_name() == 'settings') ? 'active' : '' ?>">
                    <a href="settings.php" class="nav-link"><i class="fas fa-cog" aria-hidden="true"></i> Settings</a>
                </li>

                <?php if(Session::get('rank') !== 'editor'): ?>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="navAdmin" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fas fa-user-shield" aria-hidden="true"></i> Admin</a>
                        <div class="dropdown-menu" role="menu" aria-labelledby="navAdmin">
                            <a href="<?=SROOT?>user-list.php" class="dropdown-item <?= (get_page_name() == 'user-list') ? 'active' : '' ?>">Users</a>
                            <a href="<?=SROOT?>post-list.php" class="dropdown-item <?= (get_page_name() == 'post-list') ? 'active' : '' ?>">Posts</a>
                            <a href="<?=SROOT?>comment-list.php" class="dropdown-item <?= (get_page_name() == 'comment-list') ? 'active' : '' ?>">Comments</a>
                            <?php if(Session::get('rank') == 'admin'): ?>
                                <div class="dropdown-divider"></div>
                                <a href="<?=SROOT?>withdrawal-list.php" class="dropdown-item <?= (get_page_name() == 'withdrawal-list') ? 'active' : '' ?>">Withdrawals</a>
                                <a href="<?=SROOT?>controls.php" class="dropdown-item <?= (get_page_name() == 'controls') ? 'active' : '' ?>">Controls</a>
                                <a href="<?=SROOT?>generate-key.php" class="dropdown-item <?= (get_page_name() == 'generate-key') ? 'active' : '' ?>">Generate Sub key</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?=SROOT?>replies.php" class="dropdown-item <?= (get_page_name() == 'replies') ? 'active' : '' ?>">Replies</a>
                            <a href="<?=SROOT?>site-stats.php" class="dropdown-item <?= (get_page_name() == 'site-stats') ? 'active' : '' ?>">Site stats</a>
                        </div>
                    </li>
                <?php endif; ?>

                <li class="nav-item <?= (get_page_name() == 'signout') ? 'active' : '' ?>">
                    <a href="<?=SROOT?>signout.php" class="nav-link"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Sign out</a>
                </li>

            <?php else: ?>

                <li class="nav-item <?= (get_page_name() == 'signup') ? 'active' : '' ?>">
                    <a href="<?=SROOT?>signup.php" class="nav-link"><i class="fas fa-user-plus" aria-hidden="true"></i> Sign up</a>
                </li>
                
                <li class="nav-item <?= (get_page_name() == 'signin') ? 'active' : '' ?>">
                    <a href="<?=SROOT?>signin.php" class="nav-link"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Sign in</a>
                </li>
            <?php endif; ?>

            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" id="navSupportLegal" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fas fa-question-circle" aria-hidden="true"></i> Support &amp; Legal</a>
                <div class="dropdown-menu" role="menu" aria-labelledby="navSupportLegal">
                    <a href="mailto:me@mail.com" class="dropdown-item">Contact Us</a>
                    <a href="<?=SROOT?>how-we-work.php" class="dropdown-item <?= (get_page_name() == 'how-we-work') ? 'active' : '' ?>">How We Work</a>
                    <a href="<?=SROOT?>faq.php" class="dropdown-item <?= (get_page_name() == 'faq') ? 'active' : '' ?>">FAQ</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?=SROOT?>terms.php" class="dropdown-item <?= (get_page_name() == 'terms') ? 'active' : '' ?>">Terms of Use</a>
                </div>
            </li>
        </ul>
    </div>
</nav>