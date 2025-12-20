<?php

namespace Libs\Database;

class UsersTable
{
    private $db;
    public function __construct(Mysql $mysql)
    {
        $this->db = $mysql->connect();
    }

    public function getAll()
    {
        $statement = $this->db->query(
            "SELECT * FROM users"
        );
        return $statement->fetchAll();
    }

    public function findByEmailAndPassword($email, $password)
    {
        $statement = $this->db->prepare("SELECT * FROM users WHERE email=:email");
        $statement->execute(["email" => $email]);
        $user = $statement->fetch();
        // return $user ?? false;
        if ($user) {
            if (password_verify($password, $user->password)) {
               
                return $user;
            }
        }
        return false;
    }

      public function findByUsernameAndPassword($username, $password)
    {
        $statement = $this->db->prepare("SELECT * FROM users WHERE username=:username");
        $statement->execute(["username" => $username]);
        $user = $statement->fetch();
        // return $user ?? false;
        if ($user) {
            if (password_verify($password, $user->password)) {
               
                return $user;
            }
        }
        return false;
    }
    // public function findByEmailAndPasword($email, $password)
    // {
    //     $statement = $this->db->prepare("SELECT * FROM users WHERE email=:email AND password=:password");
    //     $statement->execute([
    //         'email' => $email,
    //         'password' => $password
    //     ]);
    //     $row = $statement->fetch();
    //     return $row ?? false;
    // }

    public function insert($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $statement = $this->db->prepare(
            "INSERT INTO users(username,email,phone,address,password,created_at) VALUES (:username,:email,:phone,:address,:password,NOW())"
        );
        $statement->execute($data);
        return $this->db->lastInsertId();
    }
    // public function insert($data)
    // {
    //     $qry = "INSERT INTO users (name, email, phone, address,password,created_at) 
    //         VALUES (:name, :email, :phone, :address,:password, NOW())";
    //     $statement = $this->db->prepare($qry);
    //     $statement->execute($data);
    //     return $this->db->lastInsertId();
    // }

    public function updatePhoto($photo, $id)
    {
        $statement = $this->db->prepare("UPDATE users SET photo=:photo WHERE id=:id");
        $statement->execute(['photo' => $photo, 'id' => $id]);
        return $statement->rowCount();
    }

    public function delete($id)
    {
        $statement = $this->db->prepare("DELETE FROM users WHERE id=:id");
        $statement->execute(['id' => $id]);
        return $statement->rowCount();
    }

    public function suspend($id)
    {
        $statement = $this->db->prepare("UPDATE users SET suspended=1 WHERE id=:id");
        $statement->execute(['id' => $id]);
        return $statement->rowCount();
    }

    public function unsuspend($id)
    {
        $statement = $this->db->prepare("UPDATE users SET suspended=0 WHERE id=:id");
        $statement->execute(['id' => $id]);
        return $statement->rowCount();
    }

    public function getById($id)
    {
        $statement = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $statement->execute(['id' => $id]);
        return $statement->fetch();
    }

    public function updateProfile($id, $data)
    {
        $sql = "UPDATE users SET 
            phone = :phone,
            height = :height,
            current_weight = :current_weight,
            date_of_birth = :date_of_birth,
            gender = :gender,
            updated_at = NOW()
        WHERE id = :id";
        
        $data['id'] = $id;
        $statement = $this->db->prepare($sql);
        $statement->execute($data);
        return $statement->rowCount();
    }
}
