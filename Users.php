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

$connexion = mysqli_connect("localhost", "root", "", "classes");
$u1 = new Users($connexion);
$u1->register("Pops", "Mdp", "pauline@plateforme.io", "Pauline", "Hiez");
echo "Bonjour " . $u1->firstname . " !";
