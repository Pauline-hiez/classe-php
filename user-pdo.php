<?php

class Userpdo
{
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    private $pdo;
    private $connected = false;

    // Constructeur
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Inscription
    public function register($login, $password, $email, $firstname, $lastname)
    {
        $sql = "INSERT INTO utilisateurs (login, password, email, firstname, lastname
        VALUES (:login, :password, :email, :firstname, :lastname)";
        $stmt = $this->pdo->prepare($sql);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bindParam(':login', $login);
        $stmt->bindParam('password', $hashedPassword);
        $stmt->bindParam('email', $email);
        $stmt->bindParam('firstname', $firstname);
        $stmt->bindParam('lastname', $lastname);

        if ($stmt->execute()) {
            $this->connect($login, $password);
            return $this->getAllInfos();
        }
        return false;
    }

    // Connexion
    public function connect($login, $password)
    {
        $sql = "SELECT * FROM utilisateurs WHERE login = :login";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':login', $login);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data && password_verify($password, $data['password'])) {
            $this->id = $data['id'];
            $this->login = $data['login'];
            $this->email = $data['email'];
            $this->firstname = $data['firstname'];
            $this->lastname = $data['lastname'];
            $this->connected = true;
            return true;
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
    }

    // Supprime un utilisateur
    public function delete() {}
}
