<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'slim');
define('DB_PASS', 'slimtest');
define('DB_NAME', 'slimtest');
class PFDB {
    
    private $pdo;
    private $table;
    
    public function __construct()
    {
        $this->table = strtolower(get_class($this));
        try
        {
            $this->pdo = new PDO(sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME), DB_USER, DB_PASS);
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
    }
    private function prepare($query, $params)
    {
        $sth = $this->pdo->prepare($query);
        $i = 1;
        foreach($params as $param => $value)
        {
            $sth->bindValue($i, $value);
            $i++;
        }
        $sth->execute();
        return $sth;
    }
    public function create($data)
    {
        $query = sprintf('insert into %s ( %s ) values ( %s )', $this->table, implode(', ', array_keys($data)), implode(', ', array_fill(0, count($data), '?')));
        $sth = $this->prepare($query, $data);
        return $this->pdo->lastInsertId();
    }
    public function read($data)
    {
        $fetch = isset($data['fetch']) ? $data['fetch'] : '*';
        $order = isset($data['order']) ? $data['order'] : array();
        $howmuch = isset($data['amount']) ? $data['amount'] : 'first';
        unset($data['fetch']);
        unset($data['order']);
        unset($data['amount']);
        $query = sprintf('select %s from %s where ', is_array($fetch) ? implode(', ', $fetch) : '*', $this->table);
        foreach($data as $key => $value)
        {
            $query .= sprintf('%s = ?', $key);
            if($key != end(array_keys($data)))
            {
                $query .= ' and ';
            }
        }
        if(isset($order) && !empty($order))
        {
            $query .= ' order by ';
            foreach($order as $ordr => $by)
            {
                $query .= sprintf('%s %s', $ordr, $by);
                if($ordr != end(array_keys($order)))
                {
                    $query .= ', ';
                }
            }
        }
        $sth = $this->prepare($query, $data);
        if($howmuch == 'first')
        {
            return $sth->fetch(PDO::FETCH_ASSOC);
        }
        else if($howmuch == 'all')
        {
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return $sth->fetch(PDO::FETCH_ASSOC);
        }
    }
    public function update($data)
    {
        $query = sprintf('update %s set ', $this->table);
        $where = $data['where'];
        $newdata = array();
        unset($data['where']);
        foreach($data as $key => $value)
        {
            $query .= sprintf('%s = ?', $key);
            $newdata[$key] = $value;
            if($key != end(array_keys($data)))
            {
                $query .= ', ';
            }
        }
        $query .= ' where ';
        foreach($where as $w => $v)
        {
            $query .=  sprintf('%s = ?', $w);
            $newdata[$w] = $v;
            if($w != end(array_keys($where)))
            {
                $query .= ' and ';
            }
        }
        $sth = $this->prepare($query, $newdata);
        return $sth;
    }
    public function delete($data)
    {
        $query = sprintf('delete from %s where ', $this->table);
        foreach($data as $key => $value)
        {
            $query .= sprintf('%s = ?', $key);
        }
        $sth = $this->prepare($query, $data);
        return $sth;
    }
}