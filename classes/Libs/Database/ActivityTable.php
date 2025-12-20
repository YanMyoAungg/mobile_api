<?php

namespace Libs\Database;

class ActivityTable
{
    private $db;

    public function __construct(Mysql $mysql)
    {
        $this->db = $mysql->connect();
    }

    // Create activity
    public function create($data)
    {
        $sql = "INSERT INTO activities (
            user_id, activity_type, duration, calories_burned, activity_date
        ) VALUES (
            :user_id, :activity_type, :duration, :calories_burned, :activity_date
        )";
        
        $statement = $this->db->prepare($sql);
        $statement->execute($data);
        return $this->db->lastInsertId();
    }

    // Get activity by ID
    public function getById($id, $user_id)
    {
        $statement = $this->db->prepare("SELECT * FROM activities WHERE id = :id AND user_id = :user_id");
        $statement->execute(['id' => $id, 'user_id' => $user_id]);
        return $statement->fetch();
    }

    // Get all activities for a user
    public function getAllByUserId($user_id, $limit = 100, $offset = 0)
    {
        $statement = $this->db->prepare(
            "SELECT * FROM activities 
            WHERE user_id = :user_id 
            ORDER BY activity_date DESC 
            LIMIT :limit OFFSET :offset"
        );
        $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    // Get activities by date range
    public function getByDateRange($user_id, $start_date, $end_date)
    {
        $statement = $this->db->prepare(
            "SELECT * FROM activities 
            WHERE user_id = :user_id 
            AND DATE(activity_date) BETWEEN :start_date AND :end_date
            ORDER BY activity_date DESC"
        );
        $statement->execute([
            'user_id' => $user_id,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
        return $statement->fetchAll();
    }

    // Get activities by type
    public function getByType($user_id, $activity_type, $limit = 50)
    {
        $statement = $this->db->prepare(
            "SELECT * FROM activities 
            WHERE user_id = :user_id AND activity_type = :activity_type
            ORDER BY activity_date DESC 
            LIMIT :limit"
        );
        $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
        $statement->bindValue(':activity_type', $activity_type, \PDO::PARAM_STR);
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    // Update activity
    public function update($id, $user_id, $data)
    {
        $sql = "UPDATE activities SET 
            activity_type = :activity_type,
            duration = :duration,
            calories_burned = :calories_burned,
            activity_date = :activity_date
        WHERE id = :id AND user_id = :user_id";
        
        $data['id'] = $id;
        $data['user_id'] = $user_id;
        $statement = $this->db->prepare($sql);
        $statement->execute($data);
        return $statement->rowCount();
    }

    // Delete activity
    public function delete($id, $user_id)
    {
        $statement = $this->db->prepare("DELETE FROM activities WHERE id = :id AND user_id = :user_id");
        $statement->execute(['id' => $id, 'user_id' => $user_id]);
        return $statement->rowCount();
    }

    // Get activity statistics
    public function getStats($user_id, $start_date = null, $end_date = null)
    {
        $where = "WHERE user_id = :user_id";
        $params = ['user_id' => $user_id];

        if ($start_date && $end_date) {
            $where .= " AND DATE(activity_date) BETWEEN :start_date AND :end_date";
            $params['start_date'] = $start_date;
            $params['end_date'] = $end_date;
        }

        $sql = "SELECT 
            COUNT(*) as total_activities,
            SUM(duration) as total_duration,
            SUM(calories_burned) as total_calories
        FROM activities $where";
        
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetch();
    }
}
