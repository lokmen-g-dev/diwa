<div class="row">
    <div class="col-lg-12">
        <?php
        session_start();
        
        if (isset($_POST['email']) && isset($_POST['password'])) {
            try {
                // Sanitize user inputs
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'];

                // login
                if ($result = $model->userSignIn($email, $password, $config['system']['hashing_algorithm'])) {
                    if (false !== $result && count($result) > 0) {
                        // delete session data
                        session_unset();

                        // save user to session
                        $_SESSION['user_id'] = $result[0]['id'];

                        // Redirect to logged in page
                        header('Location: ?page=loggedin');
                        exit;
                    } else {
                        echo '<div class="alert alert-danger">Wrong E-Mail-Address or Password</div>';
                    }
                }
            } catch (Exception $ex) {
                error(500, 'Exception during login', $ex);
            }
        }
        ?>
    </div>
</div>
