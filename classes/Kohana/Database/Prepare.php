<?php
defined('SYSPATH') or die('No direct script access.');

/**
 * Class for use PDO Prepare statement.
 *
 * Sample:
 * Database::instance()->prepare('SELECT * FROM table WHERE id=:id')->bindValue(':id',$id)->execute()->fetchAll();
 *
 * @package    Kohana/Database
 * @category   Drivers
 * @author     Oleg Mikhailenko
 * @license    http://kohanaphp.com/license
 */
class Kohana_Database_Prepare {

    /**
     * @var PDO
     */
    protected $db;
    /**
     * @var PDOStatement
     */
    protected $_prepare;
    public $result;

    protected $error_execute = null;


    protected $_param = array();
    protected $_sql;

    /**
     * @param $db PDO
     * @param $sql
     */
    public function __construct($db, $sql) {

        $this->_sql = $sql;
        $this->db = $db;
        $this->_prepare = $db->prepare($sql);
    }

    /**
     * Binds a parameter to the specified variable name
     *
     * @param      $parametr
     * @param      $variables
     * @param int  $data_type
     * @param null $length
     * @param null $driver_options
     * @return Kohana_Database_Prepare
     */
    public function bindParam($parametr, $variables, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null) {

        $this->_param[] = array($parametr, $variables);
        $this->_prepare->bindParam($parametr, $variables, $data_type, $length, $driver_options);
        return $this;
    }

    /**
     * Bind a value
     *
     * @param     $parametr
     * @param     $value
     * @param int $data_type
     * @return $this
     */
    public function bindValue($parametr, $value, $data_type = PDO::PARAM_STR) {

        $this->_param[] = array($parametr, $value);
        $this->_prepare->bindValue($parametr, $value, $data_type);
        return $this;
    }

    /**
     * Execute statement
     *
     * @return Kohana_Database_Prepare
     */
    public function execute() {
        try {
            $this->result = $this->_prepare->execute();
        } catch (PDOException $e) {
            $error = Kohana_Exception::text($e);;

            Kohana::$log->add(Log::ERROR, $error);

            if (Kohana::$environment == Kohana::DEVELOPMENT) {
                $message = 'Execution query ' . $this->_sql . PHP_EOL .
                    'Params ' . print_r($this->_param, true) . PHP_EOL;

                Kohana::$log->add(Log::DEBUG, $message);
            }
            $this->error_execute = true;
            return $this;
        }
        $this->error_execute = false;

        $this->_param = array();
        return $this;
    }

    /**
     * @return array
     */
    public function fetchAll() {
        if ($this->error_execute) return false;
        return $this->_prepare->fetchAll();
    }

    /**
     * @return array
     */
    public function fetch() {

        return $this->_prepare->fetch();
    }

    /**
     * @return int
     */
    public function rowCount() {
        return $this->_prepare->rowCount();
    }
}