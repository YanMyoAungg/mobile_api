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
            "SELECT users.*,roles.name AS role FROM users LEFT JOIN roles ON users.role_id = roles.id"
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
        $statement = $this->db->prepare("SELECT * FROM users WHERE name=:username");
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
            "INSERT INTO users(name,email,phone,address,password,created_at) VALUES (:name,:email,:phone,:address,:password,NOW())"
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
    public function changeRole($id, $role_id)
    {
        $statement = $this->db->prepare("UPDATE users SET role_id=:role_id WHERE
        id=:id");
        $statement->execute(['id' => $id, 'role_id' => $role_id]);
        return $statement->rowCount();
    }
}
