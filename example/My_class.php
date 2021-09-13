<?php
require_once "../utils.php";

class My_class
{
    public static $DB;
    /* ==================================================== FIELDS ==================================================== */
    private $id;
    private $name;
    private $date;
    private $timestamp;
    private $valid;

    /* ==================================================== CONSTRUCTOR & CIE ==================================================== */
    private function __construct($id)
    {
        $this->db = getDatabase();
        $req = prepareAndFetch(My_class::$DB, "SELECT * FROM my_class WHERE id = :id", array("id" => $id));

        $this->id = $req["id"];
        $this->name = $req["name"];
        $this->date = $req["date"];
        $this->timestamp = $req["timestamp"];
        $this->valid = $req["valid"];
    }

    public static function wrapper(string $id)
    {
        $information = prepareAndFetch(My_class::$DB, "SELECT * FROM my_class WHERE id = :id", array("id" => $id));
        if (!is_array($information)) return null;
        return new self($id);
    }

    public static function bddWrapper(array $req)
    {
        return self::wrapper($req["id"]);
    }

    public static function create($id, $name, $date, $timestamp, $valid)
    {
        $req = "INSERT INTO my_class (id, name, date, timestamp, valid) VALUES (:id, :name, :date, :timestamp, :valid)";
        $tab = array("id" => $id, "name" => $name, "date" => $date, "timestamp" => $timestamp, "valid" => $valid);
        $execution = prepare(My_class::$DB, $req, $tab);
        if (dbAffected($execution)) return self::wrapper($id);
        else return null;
    }

    public function update($name, $date, $timestamp, $valid)
    {
        $req = "UPDATE my_class SET name = :name, date = :date, timestamp = :timestamp, valid = :valid WHERE id = :id";
        $tab = array("name" => $name, "date" => $date, "timestamp" => $timestamp, "valid" => $valid, "id" => $this->id);
        $execution = prepare(My_class::$DB, $req, $tab);
        if (dbAffected($execution)) {
            $this->name = $name;
            $this->date = $date;
            $this->timestamp = $timestamp;
            $this->valid = $valid;
            return true;
        }
        return false;
    }

    public function delete()
    {
        $req = "DELETE FROM my_class WHERE id = :id";
        $tab = array("id" => $this->id);
        $execution = prepare(My_class::$DB, $req, $tab);
        return dbAffected($execution);
    }

    public function __toString()
    {
        $string = "";
        $string .= "id = " . $this->getId() . "<br>";
        $string .= "name = " . $this->getName() . "<br>";
        $string .= "date = " . $this->getDate() . "<br>";
        $string .= "timestamp = " . $this->getTimestamp() . "<br>";
        $string .= "valid = " . $this->isValid() . "<br>";
        return $string;
    }

    /* ==================================================== GETTERS ==================================================== */
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDate($format = 'Y-m-d')
    {
        return formatDate($format, $this->date);
    }

    public function getTimestamp($format = 'Y-m-d H:i:s')
    {
        return formatDate($format, $this->timestamp, 'Y-m-d H:i:s');
    }

    public function isValid()
    {
        return $this->valid;
    }


    /* ==================================================== SETTERS ==================================================== */
    public function setName($name)
    {
        $req = prepare(My_class::$DB, "UPDATE my_class SET name = :name WHERE id = :id", array("id" => $this->id, "name" => $name));
        $updated = dbAffected($req);
        if ($updated) $this->name = $name;
        return $updated;
    }

    public function setDate($date)
    {
        $req = prepare(My_class::$DB, "UPDATE my_class SET date = :date WHERE id = :id", array("id" => $this->id, "date" => $date));
        $updated = dbAffected($req);
        if ($updated) $this->date = $date;
        return $updated;
    }

    public function setTimestamp($timestamp)
    {
        $req = prepare(My_class::$DB, "UPDATE my_class SET timestamp = :timestamp WHERE id = :id", array("id" => $this->id, "timestamp" => $timestamp));
        $updated = dbAffected($req);
        if ($updated) $this->timestamp = $timestamp;
        return $updated;
    }

    public function setValid($valid)
    {
        $req = prepare(My_class::$DB, "UPDATE my_class SET valid = :valid WHERE id = :id", array("id" => $this->id, "valid" => $valid));
        $updated = dbAffected($req);
        if ($updated) $this->valid = $valid;
        return $updated;
    }


    /** @return self[] */
    public static function getListMyClass()
    {
        $recherche = prepare(My_class::$DB, "SELECT * FROM my_class");
        $array = array();
        while ($element = $recherche->fetch()) {
            $array[] = self::bddWrapper($element);
        }
        return $array;
    }

}


