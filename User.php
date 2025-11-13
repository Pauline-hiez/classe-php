<?php

// Empêcher injection SQL
function esc($str)
{
    global $connexion;
    return mysqli_real_escape_string($connexion, $str);
}

// Création de la classe User
class User
{
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    private $connexion;

    public function __construct($connexion)
    {
        $this->connexion = $connexion;
    }

    // Création d'un nouvel utilisateur
    public function create($login, $password, $email, $firstname, $lastname)
    {

        // Sécurisation des données contre injections SQL
        $login = esc($this->connexion, $login);
        $email = esc($this->connexion, $email);
        $firstname = esc($this->connexion, $firstname);
        $lastname = esc($this->connexion, $lastname);
        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO utilisateurs (login, password, email, firstname, lastname)
                VALUES ('$login', '$password', '$email', '$firstname', '$lastname')";

        if (mysqli_query($this->connexion, $sql)) {
            echo "Utilisateur crée avec succès !<br>";
            return true;
        } else {
            echo "Erreur lors de la création : " . mysqli_error($this->connexion);
            return false;
        }
    }

    // Récupère un utilisateur par son Id
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
        } else {
            echo "Aucun utilisateur trouvé $id.<br>";
            return null;
        }
    }

    // Modifier un utilisateur
    public function update($id, $login, $email, $firstname, $lastname)
    {
        $id = (int)$id;
        $login = esc($this->connexion, $login);
        $email = esc($this->connexion, $email);
        $firstname = esc($this->connexion, $firstname);
        $lastname = esc($this->connexion, $lastname);

        $sql = "UPDATE utilisateurs
                SET login='$login', email='$email', firstname='$firstname', lastname='$lastname'
                WHERE id=$id";

        if (mysqli_query($this->connexion, $sql)) {
            echo "Utilisateur mis à jour avec succès ! <br>";
            return true;
        } else {
            echo "Erreur lors de la modification : " . mysqli_error($this->connexion);
            return false;
        }
    }

    // Supprimer un utilisateur
    public function delete($id)
    {
        $id = (int)$id;
        $sql = "DELETE FROM utilisateurs
                WHERE id = $id";

        if (mysqli_query($this->connexion, $sql)) {
            echo "Utilisateur supprimé avec succès !<br>";
            return true;
        } else {
            echo "Erreur lors de la suppression : " . mysqli_error($this->connexion);
            return false;
        }
    }
}
