<?php
session_start();

// Process post data
if ('post' === strtolower($_SERVER['REQUEST_METHOD']) && isset($_POST)) {
    try {
        if (!include(INSTALLATION_PATH . '/install.php')) {
            die('Error: could not include "install.php".');
        }
    } catch (Exception $ex) {
        die('Error: could not include "install.php": ' . htmlspecialchars($ex->getMessage(), ENT_QUOTES, 'UTF-8'));
    }

    // Output message
    ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="alert alert-success">
                    <p>DIWA has been setup! <a href="/">Start hacking now</a> :-)</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    return;
}

// Check PHP version
$requirementsErrors = [];
if (version_compare(phpversion(), '5.6.0', '<')) {
    $requirementsErrors[] = 'Please make sure you use PHP 5.6 or higher. PHP 7.* is recommended.';
}

?>
<div class="row">
    <div class="col-lg-12">
    <?php if (!$installation) { ?>
        <h1>DIWA Reset</h1>
        <div class="alert alert-danger">
            <p>Your DIWA seems to be properly installed already. Only proceed if you want to reset DIWA's Database.</p>
        </div>
    <?php } else { ?>
        <h1>DIWA Setup</h1>
        <div class="alert alert-info">
            <p>DIWA seems not to be installed or got essentially corrupted. Please setup DIWA now.</p>
        </div>
    <?php } ?>
    
        <h2>Preconditions</h2>
        <?php if (count($requirementsErrors) === 0) { ?>
            <form method="post" action="?page=installation" class="<?php echo ($installation ? '' : ' diwa-reset'); ?>">
                <div class="alert alert-success">
                    <p><span class="glyphicon glyphicon-ok"></span> Congratulations! All requirements have been met.</p>
                </div>
                <div>
                    <input type="submit" class="btn btn-success" value="<?php echo ($installation ? 'Setup' : 'Reset'); ?> DIWA!"/>
                </div>
            </form>
        <?php } else { ?>
            <div class="alert alert-danger">
                <p style="margin-bottom:10px;"><span class="glyphicon glyphicon-warning-sign"></span> <strong>I'm sorry, not all requirements have been met. Please fix the following <span class="badge"><?php echo count($requirementsErrors); ?></span> issues:</strong></p>
                <ul class="list-group">
                    <?php foreach ($requirementsErrors as $error) { ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </div>
</div>
