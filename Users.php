<?php

require_once "helpers.php";

// Création de la classe User
class Users
{
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    private $connexion;
    private $connected = false;

    public function __construct($connexion)
    {
        $this->connexion = $connexion;
    }

    // Crée un utilisateur 
    public function register($login, $password, $email, $firstname, $lastname)
    {
        $login = esc($login);
        $email = esc($email);
        $firstname = esc($firstname);
        $lastname = esc($lastname);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO utilisateurs (login, password, email, firstname, lastname)
                VALUES ('$login', '$passwordHash', '$email', '$firstname', '$lastname')";

        if (mysqli_query($this->connexion, $sql)) {

            // Connecte automatiquement l’utilisateur
            $this->connect($login, $password);
            return $this->getAllInfos();
        } else {
            echo "Erreur lors de l'inscription : " . mysqli_error($this->connexion);
            return false;
        }
    }

    // Connexion
    public function connect($login, $password)
    {
        $login = esc($login);
        $sql = "SELECT * FROM utilisateurs WHERE login = '$login'";
        $result = mysqli_query($this->connexion, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            if (password_verify($password, $data['password'])) {
                $this->id = $data['id'];
                $this->login = $data['login'];
                $this->email = $data['email'];
                $this->firstname = $data['firstname'];
                $this->lastname = $data['lastname'];
                $this->connected = true;
                return true;
            }
        }

        $this->connected = false;
        return false;
    }

    // Déconnexion
    public function disconnect()
    {
        $this->id = null;
        $this->login = null;
        $this->email = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->connected = false;
    }

    // Supprime utilisateur et déconnecte
    public function delete()
    {
        if ($this->connected && $this->id) {
            $sql = "DELETE FROM utilisateurs WHERE id = $this->id";
            if (mysqli_query($this->connexion, $sql)) {
                $this->disconnect();
                return true;
            }
        }
        return false;
    }

    // Met à jour l’utilisateur
    public function update($login, $password, $email, $firstname, $lastname)
    {
        if (!$this->connected || !$this->id) return false;

        $login = esc($login);
        $email = esc($email);
        $firstname = esc($firstname);
        $lastname = esc($lastname);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE utilisateurs 
                SET login ='$login', password ='$passwordHash', email ='$email', firstname ='$firstname', lastname ='$lastname'
                WHERE id=$this->id";

        if (mysqli_query($this->connexion, $sql)) {
            // Met à jour les attributs de l'objet
            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
            return true;
        }
        return false;
    }

    // Vérifie si connecté
    public function isConnected()
    {
        return $this->connected;
    }

    // Retourne toutes les informations
    public function getAllInfos()
    {
        if ($this->connected) {
            return [
                "id" => $this->id,
                "login" => $this->login,
                "email" => $this->email,
                "firstname" => $this->firstname,
                "lastname" => $this->lastname
            ];
        }
        return null;
    }

    // Getters 
    public function getLogin()
    {
        return $this->login;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getFirstname()
    {
        return $this->firstname;
    }
    public function getLastname()
    {
        return $this->lastname;
    }


    // Lis un utilisateur par son ID
    public function read($id)
    {
        $id = (int)$id;
        $sql = "SELECT * FROM utilisateurs WHERE id = $id";
        $result = mysqli_query($this->connexion, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            $this->id = $data['id'];
            $this->login = $data['login'];
            $this->email = $data['email'];
            $this->firstname = $data['firstname'];
            $this->lastname = $data['lastname'];
            return $data;
        }
        return null;
    }
}

// Connexion à la base de données
$connexion = mysqli_connect("localhost", "root", "", "classes");

if (!$connexion) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

$user = new Users($connexion);
$message = "";
$error = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'register') {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';
        $firstname = $_POST['firstname'] ?? '';
        $lastname = $_POST['lastname'] ?? '';

        if ($user->register($login, $password, $email, $firstname, $lastname)) {
            $message = "Inscription réussie !";
        } else {
            $error = "Erreur lors de l'inscription";
        }
    } elseif ($_POST['action'] === 'connect') {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($user->connect($login, $password)) {
            $message = "Connexion réussie !";
        } else {
            $error = "Identifiants incorrects";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription & Connexion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #e0e5ec 0%, #f5f7fa 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .main-container {
            width: 100%;
            max-width: 1200px;
        }

        .forms-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }

        .form-card {
            background: #e0e5ec;
            border-radius: 40px;
            padding: 50px 40px;
            box-shadow:
                20px 20px 60px #bec3c9,
                -20px -20px 60px #ffffff;
        }

        h1 {
            text-align: center;
            font-size: 2rem;
            color: #a0a4a8;
            margin-bottom: 40px;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: lowercase;
        }

        .message {
            background: #e0e5ec;
            color: #4CAF50;
            padding: 15px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow:
                inset 5px 5px 10px #bec3c9,
                inset -5px -5px 10px #ffffff;
        }

        .error {
            background: #e0e5ec;
            color: #f44336;
            padding: 15px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow:
                inset 5px 5px 10px #bec3c9,
                inset -5px -5px 10px #ffffff;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 18px 25px;
            border: none;
            border-radius: 30px;
            background: #e0e5ec;
            font-size: 1rem;
            color: #6b6e70;
            box-shadow:
                inset 8px 8px 16px #bec3c9,
                inset -8px -8px 16px #ffffff;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            box-shadow:
                inset 6px 6px 12px #bec3c9,
                inset -6px -6px 12px #ffffff;
        }

        .form-group input::placeholder {
            color: #a0a4a8;
            font-weight: 300;
        }

        .btn-primary {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 30px;
            background: #e0e5ec;
            color: #c42b2b;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow:
                8px 8px 16px #bec3c9,
                -8px -8px 16px #ffffff;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            box-shadow:
                12px 12px 24px #bec3c9,
                -12px -12px 24px #ffffff;
        }

        .btn-primary:active {
            box-shadow:
                inset 6px 6px 12px #bec3c9,
                inset -6px -6px 12px #ffffff;
        }

        .btn-primary::before {
            content: '🔒';
            font-size: 1rem;
        }

        .icon-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 35px 0 0 0;
        }

        .icon-btn {
            width: 60px;
            height: 60px;
            border: none;
            border-radius: 50%;
            background: #e0e5ec;
            color: #6b6e70;
            font-size: 1.3rem;
            cursor: pointer;
            box-shadow:
                8px 8px 16px #bec3c9,
                -8px -8px 16px #ffffff;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn:hover {
            box-shadow:
                12px 12px 24px #bec3c9,
                -12px -12px 24px #ffffff;
        }

        .icon-btn:active {
            box-shadow:
                inset 6px 6px 12px #bec3c9,
                inset -6px -6px 12px #ffffff;
        }

        .info-card {
            background: #e0e5ec;
            border-radius: 30px;
            padding: 30px;
            margin-top: 40px;
            box-shadow:
                15px 15px 30px #bec3c9,
                -15px -15px 30px #ffffff;
            grid-column: 1 / -1;
        }

        .info-card h2 {
            color: #6b6e70;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 300;
        }

        .info-card pre {
            background: #e0e5ec;
            padding: 20px;
            border-radius: 20px;
            color: #6b6e70;
            box-shadow:
                inset 8px 8px 16px #bec3c9,
                inset -8px -8px 16px #ffffff;
            overflow-x: auto;
            font-size: 0.9rem;
        }

        @media (max-width: 968px) {
            .forms-wrapper {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 40px 25px;
            }

            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="forms-wrapper">
            <!-- Formulaire d'inscription -->
            <div class="form-card">
                <h1>inscription</h1>

                <form method="POST" action="" autocomplete="off">
                    <input type="hidden" name="action" value="register">

                    <div class="form-group">
                        <input type="text" name="login" placeholder="Login" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" placeholder="Mot de passe" required>
                    </div>

                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>

                    <div class="form-group">
                        <input type="text" name="firstname" placeholder="Prénom" required>
                    </div>

                    <div class="form-group">
                        <input type="text" name="lastname" placeholder="Nom" required>
                    </div>

                    <button type="submit" class="btn-primary">S'inscrire</button>

                    <div class="icon-buttons">
                        <button type="button" class="icon-btn">←</button>
                    </div>
                </form>
            </div>

            <!-- Formulaire de connexion -->
            <div class="form-card">
                <h1>connexion</h1>

                <form method="POST" action="" autocomplete="off">
                    <input type="hidden" name="action" value="connect">

                    <div class="form-group">
                        <input type="text" name="login" placeholder="Login" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" placeholder="Mot de passe" required>
                    </div>

                    <button type="submit" class="btn-primary">Se connecter</button>

                    <div class="icon-buttons">
                        <button type="button" class="icon-btn">←</button>
                    </div>
                </form>
            </div>

            <?php if ($user->isConnected()): ?>
                <div class="info-card">
                    <h2>Informations utilisateur</h2>
                    <pre><?php print_r($user->getAllInfos()); ?></pre>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>