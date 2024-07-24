<div class="row">
    <div class="col-lg-12">
        <?php
        if (isset($_POST['email']) && isset($_POST['password'])) {
            try {
                // Connexion à la base de données SQLite
                $pdo = new PDO('sqlite:/mnt/data/db.s3db');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Préparer la requête SQL
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND password = :password');

                // Hasher le mot de passe avec l'algorithme spécifié
                $hashedPassword = hash($config['system']['hashing_algorithm'], $_POST['password']);

                // Lier les paramètres
                $stmt->bindParam(':email', $_POST['email']);
                $stmt->bindParam(':password', $hashedPassword);

                // Exécuter la requête
                $stmt->execute();

                // Récupérer les résultats
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Vérifier les résultats et gérer la session
                if (false !== $result && 0 < count($result)) {
                    // Supprimer les données de session
                    session_unset();

                    // Sauvegarder l'utilisateur dans la session
                    $_SESSION['user_id'] = $result[0]['id'];

                    // Redirection après connexion réussie
                    header('Location: ?page=loggedin');
                    exit();
                } else {
                    echo '<div class="alert alert-danger">Wrong E-Mail-Address or Password</div>';
                }
            } catch (Exception $ex) {
                error(500, 'Exception during login', $ex);
            }
        }
        ?>
    </div>
</div>
