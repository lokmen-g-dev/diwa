<?php

class Model {

    private $db = null;
    private $prefix = null;

    public function __construct($pDatabase, $pPrefix) {
        $this->db = $pDatabase;
        $this->prefix = $pPrefix;
    }

    // ============================
    // USERS
    // ============================

    public function userSignIn($pEmail, $pPassword, $pHashingAlgorithm) {
        try {
            $sql = 'SELECT * FROM ' . $this->prefix . 'users WHERE email = :email AND password = :password';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $pEmail,
                ':password' => hash($pHashingAlgorithm, $pPassword)
            ]);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function isUserEmailInUse($pEmail) {
        try {
            $sql = 'SELECT * FROM ' . $this->prefix . 'users WHERE email = :email';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $pEmail]);
            $result = $stmt->fetchAll();
            return (false !== $result && 0 < count($result));
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function isUsernameInUse($pUsername) {
        try {
            $sql = 'SELECT * FROM ' . $this->prefix . 'users WHERE username = :username';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => $pUsername]);
            $result = $stmt->fetchAll();
            return (false !== $result && 0 < count($result));
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function createUser($pUsername, $pPassword, $pEmail, $pCountry, $pHashingAlgorithm) {
        try {
            $sql = 'INSERT INTO ' . $this->prefix . 'users (username, password, email, country, is_admin) VALUES (:username, :password, :email, :country, 0)';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':username' => $pUsername,
                ':password' => hash($pHashingAlgorithm, $pPassword),
                ':email' => $pEmail,
                ':country' => $pCountry
            ]);
            return ($result !== false);
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function getUserData($pUserId) {
        try {
            $sql = 'SELECT * FROM ' . $this->prefix . 'users WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $pUserId]);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function editUser($pUserId, $pEmail, $pCountry, $pChangePassword, $pChangeAdmin) {
        try {
            // change password?
            $passwordSql = '';
            $params = [
                ':email' => $pEmail,
                ':country' => $pCountry,
                ':id' => $pUserId
            ];
            if (null !== $pChangePassword) {
                $passwordSql = ', password = :password';
                $params[':password'] = $pChangePassword;
            }

            // change admin status?
            $adminSql = '';
            if (null !== $pChangeAdmin) {
                $adminSql = ', is_admin = :is_admin';
                $params[':is_admin'] = $pChangeAdmin;
            }

            $sql = 'UPDATE ' . $this->prefix . 'users SET email = :email, country = :country' . $passwordSql . $adminSql . ' WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            return ($result !== false);
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function getAllUsers() {
        try {
            $sql = 'SELECT id, username, email, country, is_admin FROM ' . $this->prefix . 'users';
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function getAllAdmins() {
        try {
            $sql = 'SELECT id, username, email, country, is_admin FROM ' . $this->prefix . 'users WHERE is_admin = 1';
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function removeUser($pUserId) {
        try {
            $sql = 'DELETE FROM ' . $this->prefix . 'users WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $pUserId]);
            return ($result !== false);
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    // ============================
    // DOWNLOADS
    // ============================

    public function getDownloads($pApproved) {
        try {
            $sql = 'SELECT * FROM ' . $this->prefix . 'downloads WHERE approved = :approved';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':approved' => $pApproved ? 1 : 0]);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function createDownload($pAllowGuests, $pApproved, $pTitle, $pDescription, $pFile) {
        try {
            $sql = 'INSERT INTO ' . $this->prefix . 'downloads (allow_guests, approved, title, description, file) VALUES (:allow_guests, :approved, :title, :description, :file)';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':allow_guests' => $pAllowGuests ? 1 : 0,
                ':approved' => $pApproved ? 1 : 0,
                ':title' => $pTitle,
                ':description' => $pDescription,
                ':file' => $pFile
            ]);
            return ($result !== false);
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function approveDownload($pId, $pAllowGuests) {
        try {
            $sql = 'UPDATE ' . $this->prefix . 'downloads SET approved = 1, allow_guests = :allow_guests WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':allow_guests' => $pAllowGuests ? 1 : 0,
                ':id' => $pId
            ]);
            return ($result !== false);
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    public function removeDownload($pId) {
        try {
            $sql = 'DELETE FROM ' . $this->prefix . 'downloads WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $pId]);
            return ($result !== false);
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }

    }

    // ============================
    // BOARD
    // ============================

    function getAllThreads() {
        try {
            $sql = '
                SELECT
                  t.id AS id,
                  t.title AS title,
                  t.admins_only AS admins_only,
                  MAX(p.timestamp) AS last_post,
                  COUNT(*) AS count_post
                FROM
                  ' . $this->prefix . 'threads t,
                  ' . $this->prefix . 'posts p
                WHERE
                  p.thread_id = t.id
                GROUP BY
                  t.id
                ORDER BY
                  last_post DESC;';
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    function getThread($pThreadId) {
        try {
            $sql = '
                SELECT
                  t.id AS id,
                  t.title AS title,
                  t.admins_only AS admins_only,
                  MAX(p.timestamp) AS last_post,
                  COUNT(*) AS count_post
                FROM
                  ' . $this->prefix . 'threads t,
                  ' . $this->prefix . 'posts p
                WHERE
                  t.id = :thread_id AND
                  p.thread_id = t.id
                GROUP BY
                  t.id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':thread_id' => $pThreadId]);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    function getPosts($pThreadId) {
        try {
            $sql = '
                SELECT
                  p.*,
                  u.username
                FROM
                  ' . $this->prefix . 'posts p,
                  ' . $this->prefix . 'users u
                WHERE
                    p.thread_id = :thread_id AND
                    p.user_id = u.id
                ORDER BY
                    p.id ASC';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':thread_id' => $pThreadId]);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    function createThread($pTitle, $pAdminsOnly) {
        try {
            $sql = 'INSERT INTO ' . $this->prefix . 'threads (title, admins_only) VALUES (:title, :admins_only)';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':title' => $pTitle,
                ':admins_only' => $pAdminsOnly ? 1 : 0
            ]);
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    function createPost($pThreadId, $pUserId, $pText) {
        try {
            $sql = 'INSERT INTO ' . $this->prefix . 'posts (thread_id, user_id, text) VALUES (:thread_id, :user_id, :text)';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':thread_id' => $pThreadId,
                ':user_id' => $pUserId,
                ':text' => $pText
            ]);
            return ($result !== false);
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    function getPost($pPostId) {
        try {
            $sql = '
                SELECT
                  *
                FROM
                  ' . $this->prefix . 'posts
                WHERE
                  id = :post_id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':post_id' => $pPostId]);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    function editPost($pPostId, $pPost) {
        try {
            $sql = 'UPDATE ' . $this->prefix . 'posts SET text = :text WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':text' => $pPost,
                ':id' => $pPostId
            ]);
            return ($result !== false);
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }

    function getPostsByUser($pUserId) {
        try {
            $sql = '
                SELECT
                  t.id AS thread_id,
                  t.title AS thread_title,
                  t.admins_only AS thread_admins_only,
                  p.id AS post_id,
                  p.timestamp AS post_timestamp,
                  p.text AS post_text
                FROM
                  ' . $this->prefix . 'posts p,
                  ' . $this->prefix . 'threads t
                WHERE
                  p.user_id = :user_id AND
                  p.thread_id = t.id
                ORDER BY
                  p.timestamp DESC';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $pUserId]);
            return $stmt->fetchAll();
        } catch (Exception $ex) {
            error(500, 'Query could not be executed', $ex);
        }
    }
}
