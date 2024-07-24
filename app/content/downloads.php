<?php
session_start();

// Verify if the user is logged in and is an admin
$loggedIn = isLoggedIn();
$isAdmin = (isset($_SESSION['user']['is_admin']) && 1 == $_SESSION['user']['is_admin']);
$reviewMode = (isset($_GET['review']) && '1' == $_GET['review']);

// Initialize error variable
$error = false;

// Process review actions (approve / delete)
try {
    if (isset($_GET['approve']) && !empty($_GET['approve'])) {
        // Sanitize the input
        $approveId = intval($_GET['approve']);
        $allowGuests = '';
        if (isset($_GET['guests']) && '1' === $_GET['guests']) {
            $allowGuests = true;
        }
        if ($model->approveDownload($approveId, $allowGuests)) {
            // Redirect
            header('Location: ?page=downloads&review=1&approved=1');
            exit;
        } else {
            $error = 'The File could not be published.';
        }
    } elseif (isset($_GET['delete']) && !empty($_GET['delete'])) {
        // Sanitize the input
        $deleteId = intval($_GET['delete']);
        if ($model->removeDownload($deleteId)) {
            // Redirect
            header('Location: ?page=downloads' . ($reviewMode ? '&review=1' : '') . '&deleted=1');
            exit;
        } else {
            $error = 'The File could not be deleted.';
        }
    }
} catch (Exception $ex) {
    error(500, 'Could not execute given (approve/delete) action', $ex);
}

// Get all downloads
try {
    $result = $model->getDownloads(($reviewMode ? 0 : 1));
} catch (Exception $ex) {
    error(500, 'Could not query downloads from Database', $ex);
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1>Downloads
            <?php if ($loggedIn) { ?>
                <?php if ($isAdmin) { ?>
                    <div class="pull-right btn-group">
                        <?php if ($reviewMode) { ?>
                            <a href="?page=downloads" class="btn btn-default"><?php echo icon('download-alt'); ?> Switch to Download Mode</a>
                        <?php } else { ?>
                            <a href="?page=downloads&review=1" class="btn btn-default"><?php echo icon('eye-open'); ?> Switch to Review Mode</a>
                        <?php } ?>
                        <a href="?page=upload" class="btn btn-primary"><?php echo icon('cloud-upload'); ?> Upload a File</a>
                    </div>
                <?php } else { ?>
                    <a href="?page=upload" class="btn btn-primary pull-right">Recommend a File</a>
                <?php } ?>
            <?php } ?>
        </h1>
        <?php if (!$loggedIn) { ?>
            <div class="alert alert-warning">Please log-in to see all Downloads.</div>
        <?php } ?>
        <?php if ($reviewMode) { ?>
            <div class="alert alert-warning">You are in review mode where you can approve or delete new files.</div>
        <?php } ?>
        <?php if ($error !== false) { ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php } elseif (isset($_GET['approved']) && 1 == $_GET['approved']) { ?>
            <div class="alert alert-success">The File has been approved and published.</div>
        <?php } elseif (isset($_GET['deleted']) && 1 == $_GET['deleted']) { ?>
            <div class="alert alert-success">The File has been deleted.</div>
        <?php } ?>
    </div>
</div>

<?php
$count = 0;
foreach ($result as $download) {
    if (1 == $download['allow_guests'] || $loggedIn) {
        if ($count === 0) {
            echo '<div class="row">';
        }
        ?>
        <div class="col-lg-6">
            <div class="panel panel-primary">
                <div class="panel-heading"><?php echo (1 == $download['allow_guests'] ? '' : icon('user', 'User only Download')); ?> <strong><?php echo htmlspecialchars($download['title']); ?></strong></div>
                <div class="panel-body"><p><?php echo htmlspecialchars($download['description']); ?></p></div>
                <div class="panel-footer text-center">
                    <div class="btn-group">
                        <a href="download.php?file=<?php echo htmlspecialchars($download['file']); ?>" class="btn btn-default"><?php echo icon('download-alt'); ?> <strong><?php echo htmlspecialchars($download['file']); ?></strong></a>
                        <?php if ($isAdmin) { ?>
                            <a href="?page=downloads<?php echo ($reviewMode ? '&review=1' : ''); ?>&delete=<?php echo htmlspecialchars($download['id']); ?>" class="btn btn-danger remove-file" title="Delete the file" onclick="return confirm('Are you sure you want to delete this file?');"><?php echo icon('remove'); ?> Delete</a>
                            <?php if ($reviewMode) { ?>
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle" type="button" data-toggle="dropdown"><?php echo icon('ok'); ?> Approve <span class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li><a href="?page=downloads&review=1&approve=<?php echo htmlspecialchars($download['id']); ?>"><?php echo icon('user'); ?> To Users Only</a></li>
                                        <li><a href="?page=downloads&review=1&approve=<?php echo htmlspecialchars($download['id']); ?>&guests=1"><?php echo icon('ok'); ?> To Users & Guests</a></li>
                                    </ul>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if ($count === 1) {
            echo '</div>';
            $count = 0;
        } else {
            $count++;
        }
    }
}
?>
