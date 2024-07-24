<?php
session_start();

// make sure only registered users can enter this site
if (!isset($_SESSION['user_id']) || 1 != $_SESSION['user']['is_admin']) {
    echo getForbiddenMessage('This Site is for Administrators only.');
    return;
}

$deleted = null;

// delete user
try {
    if (isset($_GET['remove'])) {
        $removeUserId = intval($_GET['remove']);
        if ($removeUserId !== $_SESSION['user_id']) {
            $deleted = $model->removeUser($removeUserId);
        } else {
            $deleted = false;
        }
    }
} catch (Exception $ex) {
    error(500, 'Could not delete given User from Database', $ex);
}

// get all users
try {
    $result = $model->getAllUsers();
} catch (Exception $ex) {
    error(500, 'Could not query Users from Database', $ex);
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1>Administration</h1>

        <?php
        if ($deleted === true) {
            echo '<div class="alert alert-success">The user has been deleted.</div>';
        } elseif ($deleted === false) {
            echo '<div class="alert alert-danger">The user could not be deleted.</div>';
        }
        ?>

        <?php if (false !== $result && 0 < count($result)) { ?>
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>E-Mail</th>
                    <th>Country</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($result as $user) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><a href="/?page=profile&id=<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['username']); ?></a></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['country']); ?></td>
                        <td><?php echo (1 == $user['is_admin'] ? icon('ok') : ''); ?></td>
                        <td>
                            <a href="?page=editprofile&id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-default" title="Edit User"><?php echo icon('edit'); ?> Edit</a>
                            <a href="?page=users&remove=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-default remove-user" title="Remove User" onclick="return confirm('Are you sure you want to remove this user?');"><?php echo icon('remove'); ?> Remove</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>

    </div>
</div>

<script>
    document.querySelectorAll('.remove-user').forEach(function(element) {
        element.addEventListener('click', function(event) {
            if (!confirm('Are you sure you want to remove this user?')) {
                event.preventDefault();
            }
        });
    });
</script>
