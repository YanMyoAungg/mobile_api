<?php

namespace Libs\Database;

class WeeklyGoalTable
{
    private $db;

    public function __construct(Mysql $mysql)
    {
        $this->db = $mysql->connect();
    }

    public function createOrUpdate($user_id, $target_calories)
    {
        // Check if goal exists
        $statement = $this->db->prepare("SELECT id FROM weekly_goals WHERE user_id = :user_id");
        $statement->execute(['user_id' => $user_id]);
        $existing = $statement->fetch();

        if ($existing) {
            $sql = "UPDATE weekly_goals SET target_calories = :target_calories WHERE user_id = :user_id";
        } else {
            $sql = "INSERT INTO weekly_goals (user_id, target_calories) VALUES (:user_id, :target_calories)";
        }

        $statement = $this->db->prepare($sql);
        $statement->execute([
            'user_id' => $user_id,
            'target_calories' => $target_calories
        ]);

        return $statement->rowCount();
    }

    public function getByUserId($user_id)
    {
        $statement = $this->db->prepare("SELECT * FROM weekly_goals WHERE user_id = :user_id");
        $statement->execute(['user_id' => $user_id]);
        return $statement->fetch();
    }
}
