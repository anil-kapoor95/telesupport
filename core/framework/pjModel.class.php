<?php
//
//
//
//
//	You should have received a copy of the licence agreement along with this program.
//
//	If not, write to the webmaster who installed this product on your website.
//
//	You MUST NOT modify this file. Doing so can lead to errors and crashes in the software.
//
//
//
//
?>
<?php
if (!defined("ROOT_PATH")) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}
require_once dirname(__FILE__) . "/pjApps.class.php";
class pjModel extends pjObject
{
    public $ClassFile = __FILE__;
    private $affectedRows = -1;
    private $arBatch = [];
    private $arBatchFields = [];
    private $arData = [];
    private $arDebug = false;
    private $arDistinct = false;
    private $arFrom = null;
    private $arGroupBy = null;
    private $arHaving = null;
    private $arIndex = null;
    private $arJoin = [];
    private $arOffset = null;
    private $arOrderBy = null;
    private $arRowCount = null;
    private $arSelect = [];
    private $arWhere = [];
    private $arWhereIn = [];
    private $assocTypes = [
        "hasOne",
        "hasMany",
        "belongsTo",
        "hasAndBelongsToMany",
    ];
    protected $belongsTo = null;
    private $data = [];
    private $dbo = null;
    private $errors = [];
    protected $hasAndBelongsToMany = null;
    protected $hasMany = null;
    protected $hasOne = null;
    private $initialized = false;
    private $insertId = false;
    private $joinArr = [
        "LEFT",
        "RIGHT",
        "OUTER",
        "INNER",
        "LEFT OUTER",
        "RIGHT OUTER",
        "CROSS",
        "NATURAL",
        "STRAIGHT",
    ];
    private $prefix = null;
    protected $primaryKey = null;
    protected $schema = [];
    private $scriptPrefix = null;
    private $statement = null;
    protected $table = null;
    protected $i18n = [];
    private $transactionStarted = false;
    protected $validate = [];
    public function __construct($attr = [])
    {
        if (defined("PJ_PREFIX")) {
            $this->setPrefix(PJ_PREFIX);
        }
        if (defined("PJ_SCRIPT_PREFIX")) {
            $this->scriptPrefix = PJ_SCRIPT_PREFIX;
        }
        $registry = pjRegistry::getInstance();
        if ($registry->is("dbo")) {
            $this->dbo = $registry->get("dbo");
            $this->initialized = true;
        } else {
            $driver = function_exists("mysqli_connect")
                ? "pjMysqliDriver"
                : "pjMysqlDriver";
            $params = [
                "hostname" => PJ_HOST,
                "username" => PJ_USER,
                "password" => PJ_PASS,
                "database" => PJ_DB,
            ];
            if (strpos($params["hostname"], ":") !== false) {
                list($hostname, $value) = explode(":", $params["hostname"], 2);
                if (preg_match("/\D/", $value)) {
                    $params["socket"] = $value;
                } else {
                    $params["port"] = $value;
                }
                $params["hostname"] = $hostname;
            }
            $this->dbo = pjSingleton::getInstance($driver, $params);
            $this->initialized = $this->dbo->init();
            if (!$this->initialized) {
                die($this->dbo->connectError());
            }
            $registry->set("dbo", $this->dbo);
        }
        $this->setAttributes($attr);
        return $this;
    }
    
    public function afterDelete($method)
    {
        
        return true;
    }
    
    public function afterFind()
    {
        
        return true;
    }
    
    public function afterSave($method)
    {
        
        return true;
    }
    
    public function autocommit($value = 0)
    {
        
        if (!in_array($value, [0, 1])) {
            return false;
        }
        if (
            !$this->transactionStarted &&
            $this->prepare("SET autocommit = " . $value)
                ->exec()
                ->dbo->getResult()
        ) {
            $this->transactionStarted = true;
            return true;
        }
        return false;
    }
    
    public function beforeDelete($method)
    {
        
        return true;
    }
    
    public function beforeFind()
    {
        
        return true;
    }
    
    public function beforeSave($method)
    {
        
        return true;
    }
    
    public function begin()
    {
        
        if (
            !$this->transactionStarted &&
            $this->prepare("START TRANSACTION")
                ->exec()
                ->dbo->getResult()
        ) {
            $this->transactionStarted = true;
            return true;
        }
        return false;
    }
    
    private function buildSave($type = null)
    {
        
        $save = [];
        $data = $this->getAttributes();
        foreach ($this->schema as $field) {
            if (isset($data[$field["name"]])) {
                if (!is_array($data[$field["name"]])) {
                    if (!isset($field["encrypt"])) {
                        $save[] = sprintf(
                            "`%s` = %s",
                            $field["name"],
                            preg_match(
                                "/^:[a-zA-Z]{1}.*/",
                                $data[$field["name"]]
                            )
                                ? substr($data[$field["name"]], 1)
                                : $this->escapeValue($data[$field["name"]])
                        );
                    } else {
                        switch (strtoupper($field["encrypt"])) {
                            case "AES":
                                $save[] = sprintf(
                                    "`%s` = %s",
                                    $field["name"],
                                    $this->aesEncrypt($data[$field["name"]])
                                );
                                break;
                        }
                    }
                }
            } else {
                if (!is_null($type) && $type == "insert") {
                    $save[] =
                        "`" .
                        $field["name"] .
                        "` = " .
                        (strpos($field["default"], ":") === 0
                            ? substr($field["default"], 1)
                            : "'" .
                                $this->escape(
                                    $field["default"],
                                    null,
                                    $field["type"]
                                ) .
                                "'");
                }
            }
        }
        return $save;
    }
    
    private function buildSelect()
    {
        
        $sql = "";
        $sql .= !$this->arDistinct ? "SELECT " : "SELECT DISTINCT ";
        if (count($this->arSelect) === 0) {
            $tmp = [];
            foreach ($this->schema as $field) {
                if (!isset($field["encrypt"])) {
                    $tmp[] = "t1." . $field["name"];
                } else {
                    switch (strtoupper($field["encrypt"])) {
                        case "AES":
                            $tmp[] = sprintf(
                                "%s AS `%s`",
                                $this->aesDecrypt($field["name"]),
                                $field["name"]
                            );
                            break;
                    }
                }
            }
            $sql .= join(", ", $tmp);
        } else {
            $sql .= join(", ", $this->arSelect);
        }
        $sql .= "\n";
        $sql .=
            "FROM " .
            (empty($this->arFrom) ? $this->getTable() : $this->arFrom) .
            " AS t1";
        $sql .= "\n";
        if (!empty($this->arIndex)) {
            $sql .= $this->arIndex;
            $sql .= "\n";
        }
        if (count($this->arJoin) > 0) {
            $sql .= join("\n", $this->arJoin);
            $sql .= "\n";
        }
        if (is_array($this->arWhere) && count($this->arWhere) > 0) {
            $sql .= "WHERE " . join("\n", $this->arWhere);
            $sql .= "\n";
        }
        if (!empty($this->arGroupBy)) {
            $sql .= "GROUP BY " . $this->arGroupBy;
            $sql .= "\n";
        }
        if (!empty($this->arHaving)) {
            $sql .= "HAVING " . $this->arHaving;
            $sql .= "\n";
        }
        if (!empty($this->arOrderBy)) {
            $sql .= "ORDER BY " . $this->arOrderBy;
            $sql .= "\n";
        }
        if ((int) $this->arRowCount > 0) {
            $sql .=
                "LIMIT " .
                (int) $this->arOffset .
                ", " .
                (int) $this->arRowCount;
        }
        return $sql;
    }
    
    public function commit()
    {
        
        if (
            $this->transactionStarted &&
            $this->prepare("COMMIT")
                ->exec()
                ->dbo->getResult()
        ) {
            $this->transactionStarted = false;
            return true;
        }
        return false;
    }
    
    public function debug($val)
    {
       
        $this->arDebug = (bool) $val;
        return $this;
    }
    
    public function distinct($val)
    {
       
        $this->arDistinct = is_bool($val) ? $val : true;
        return $this;
    }
    
    public function erase()
    {
        
        if ($this->beforeDelete("erase")) {
            $sql = sprintf(
                "DELETE FROM `%s` WHERE `%s` = '%s' LIMIT 1",
                $this->getTable(),
                $this->primaryKey,
                $this->arData[$this->primaryKey]
            );
            if (false !== $this->dbo->query($sql)) {
                $this->affectedRows = $this->dbo->affectedRows();
                $this->afterDelete("erase");
            } else {
                die($this->dbo->error());
            }
        }
        return $this;
    }
    
    public function eraseAll()
    {
        
        if ($this->beforeDelete("eraseAll")) {
            $sql = "";
            $sql .= sprintf(
                "DELETE FROM `%s`",
                empty($this->arFrom) ? $this->getTable() : $this->arFrom
            );
            $sql .= "\n";
            if (is_array($this->arWhere) && count($this->arWhere) > 0) {
                $sql .= "WHERE " . join("\n", $this->arWhere);
                $sql .= "\n";
            }
            if (!empty($this->arOrderBy)) {
                $sql .= "ORDER BY " . $this->arOrderBy;
                $sql .= "\n";
            }
            if ((int) $this->arRowCount > 0) {
                $sql .= "LIMIT " . (int) $this->arRowCount;
            }
            if ($this->arDebug) {
                printf("<pre>%s</pre>", $sql);
            }
            if (false !== $this->dbo->query($sql)) {
                $this->affectedRows = $this->dbo->affectedRows();
                $this->afterDelete("eraseAll");
            } else {
                die($this->dbo->error());
            }
        }
        return $this;
    }
    
    public function escape($value, $column = null, $type = null)
    {
        
        if (is_null($type) && !is_null($column)) {
            $type = $this->getColumnType($column);
        }
        switch ($type) {
            case "null":
            case "tinyblob":
            case "mediumblob":
            case "blob":
            case "longblob":
                return $value;
                break;
            case "int":
            case "smallint":
            case "tinyint":
            case "mediumint":
            case "bigint":
                return intval($value);
                break;
            case "float":
            case "decimal":
            case "double":
            case "real":
                return floatval($value);
                break;
            case "string":
            case "varchar":
            case "enum":
            case "set":
            case "char":
            case "text":
            case "tinytext":
            case "mediumtext":
            case "longtext":
            case "date":
            case "datetime":
            case "year":
            case "time":
            case "timestamp":
            default:
                return $this->escapeStr($value);
                break;
        }
    }
    
    public function escapeStr($value)
    {
       
        return $this->dbo->escapeString($value);
    }
    
    private function escapeValue($str)
    {
       
        if (is_string($str) && strlen($str) > 0) {
            return "'" . $this->escapeStr($str) . "'";
        }
        if (is_bool($str)) {
            return $str === false ? 0 : 1;
        }
        if (is_numeric($str)) {
            return $str;
        }
        if (is_null($str) || empty($str)) {
            return "NULL";
        }
        return $str;
    }
    
    public function exec($params = [])
    {
        
        $sql = $this->statement;
        foreach ($params as $key => $value) {
            $sql = str_replace(":" . $key, $this->escapeValue($value), $sql);
        }
        if ($this->arDebug) {
            printf("<pre>%s</pre>", $sql);
        }
        $special = ['\x00', '\n', '\r', "'", '"', '\x1a', "\\"];
        foreach ($special as $str) {
            if (strpos($this->statement, $str) !== false) {
                trigger_error(
                    sprintf(
                        "Illegal string found: <code>%s</code> in: %s",
                        $str,
                        $this->statement
                    ),
                    E_USER_WARNING
                );
                exit();
            }
        }
        if (false !== $this->dbo->query($sql)) {
            $this->dbo->fetchAssoc();
            $this->data = $this->dbo->getData();
            $this->affectedRows = $this->dbo->affectedRows();
            $this->insertId = $this->dbo->insertId();
        } else {
            die($this->dbo->error());
        }
        return $this;
    }
    
    public function find($pk)
    {
        
        if ($this->beforeFind()) {
            $this->arWhere = [];
            $this->arHaving = null;
            $this->arIndex = null;
            $this->arGroupBy = null;
            $this->arOrderBy = null;
            $this->arDistinct = false;
            $this->limit(1, 0)->where("t1." . $this->primaryKey, $pk);
            $sql = $this->buildSelect();
            if ($this->arDebug) {
                printf("<pre>%s</pre>", $sql);
            }
            if (false !== $this->dbo->query($sql)) {
                $this->dbo->fetchAssoc();
                $this->afterFind();
                $this->data =
                    count($this->dbo->getData()) > 0
                        ? $this->dbo->getData(0)
                        : [];
                $this->setAttributes($this->data);
            } else {
                die($this->dbo->error());
            }
        }
        return $this;
    }
    
    public function findAll()
    {
       
        if ($this->beforeFind()) {
            $sql = $this->buildSelect();
            if ($this->arDebug) {
                printf("<pre>%s</pre>", $sql);
            }
            if (false !== $this->dbo->query($sql)) {
                $this->dbo->fetchAssoc();
                $this->afterFind();
                $this->data = $this->dbo->getData();
            } else {
                die($this->dbo->error());
            }
        }
        return $this;
    }
    
    public function findCount()
    {
       
        $sql = "";
        $sql .= "SELECT COUNT(*) AS `cnt`";
        $sql .= "\n";
        $sql .= sprintf(
            "FROM `%s` AS t1",
            !empty($this->arFrom) ? $this->arFrom : $this->getTable()
        );
        $sql .= "\n";
        if (!empty($this->arIndex)) {
            $sql .= $this->arIndex;
            $sql .= "\n";
        }
        if (count($this->arJoin) > 0) {
            $sql .= join("\n", $this->arJoin);
            $sql .= "\n";
        }
        if (is_array($this->arWhere) && count($this->arWhere) > 0) {
            $sql .= "WHERE " . join("\n", $this->arWhere);
            $sql .= "\n";
        }
        if (!empty($this->arGroupBy)) {
            $sql .= "GROUP BY " . $this->arGroupBy;
            $sql .= "\n";
        }
        if (!empty($this->arHaving)) {
            $sql .= "HAVING " . $this->arHaving;
            $sql .= "\n";
        }
        if (!empty($this->arGroupBy)) {
            $sql = sprintf("SELECT COUNT(*) AS `cnt` FROM (%s) AS `tmp`", $sql);
            $sql .= "\n";
        }
        $sql .= "LIMIT 1";
        if ($this->arDebug) {
            printf("<pre>%s</pre>", $sql);
        }
        if (false !== $this->dbo->query($sql)) {
            $this->dbo->fetchRow();
            $this->data = $this->dbo->getData(0);
        } else {
            die($this->dbo->error());
        }
        return $this;
    }
    
    public function from($table, $escape = true)
    {
        
        if ((bool) $escape === true) {
            $this->arFrom = $this->escapeStr($table);
        } else {
            $this->arFrom = $table;
        }
        return $this;
    }
    
    public function getAffectedRows()
    {
        
        return $this->affectedRows;
    }
    
    public function getAssocTypes()
    {
       
        return $this->assocTypes;
    }
    
    public function getAttributes()
    {
        
        $attr = [];
        foreach ($this->schema as $field) {
            $attr[$field["name"]] = null;
            if (isset($this->arData[$field["name"]])) {
                $attr[$field["name"]] = $this->arData[$field["name"]];
            }
        }
        return $attr;
    }
    
    public function getColumnType($column)
    {
        
        foreach ($this->schema as $col) {
            if ($col["name"] == $column) {
                return $col["type"];
            }
        }
        return false;
    }
    
    public function getColumns()
    {
        
        $this->prepare(
            sprintf("SHOW COLUMNS FROM `%s`", $this->getTable())
        )->exec();
        return $this;
    }
    
    public function getData()
    {
        
        return $this->data;
    }
    
    public function getDataSlice(
        $offset,
        $length = null,
        $preserve_keys = false
    ) {
       
        if (is_null($length)) {
            $length = count($this->data) - $offset;
        }
        return array_slice($this->data, $offset, $length, $preserve_keys);
    }
    
    public function getDataIndex($index)
    {
        
        return !empty($this->data) && isset($this->data[$index])
            ? $this->data[$index]
            : false;
    }
    
    public function getDataPair($key = null, $value = null)
    {
        
        $arr = [];
        foreach ($this->data as $item) {
            if ($key !== null) {
                $arr[$item[$key]] = !is_null($value) ? $item[$value] : $item;
            } else {
                $arr[] = !is_null($value) ? $item[$value] : $item;
            }
        }
        return $arr;
    }
    
    public function getErrors()
    {
        
        return $this->errors;
    }
    
    public function getI18n()
    {
        
        return $this->i18n;
    }
    
    public function getInitialized()
    {
        
        return $this->initialized;
    }
    
    public function getInsertId()
    {
       
        return $this->insertId;
    }
    
    public function getResult()
    {
        
        return $this->dbo->getResult();
    }
    
    public function getSchema()
    {
        
        return $this->schema;
    }
    
    public function getTable()
    {
        
        return $this->prefix . $this->scriptPrefix . $this->table;
    }
    
    public function groupBy($group, $escape = true)
    {
        
        if ((bool) $escape === true) {
            $this->arGroupBy = $this->escapeStr($group);
        } else {
            $this->arGroupBy = $group;
        }
        return $this;
    }
    
    public function hasColumn($columnName)
    {
       
        foreach ($this->schema as $field) {
            if ($field["name"] == $columnName) {
                return true;
            }
        }
        return false;
    }
    
    private function hasOperator($str)
    {
        
        $str = trim($str);
        if (!preg_match("/(\s|<|>|!|=|IS NULL|IS NOT NULL)/i", $str)) {
            return false;
        }
        return true;
    }
    
    public function having($val, $escape = true)
    {
        
        if ((bool) $escape === true) {
            $this->arHaving = $this->escapeStr($val);
        } else {
            $this->arHaving = $val;
        }
        return $this;
    }
    
    public function index($val, $escape = true)
    {
       
        if ((bool) $escape === true) {
            $this->arIndex = $this->escapeStr($val);
        } else {
            $this->arIndex = $val;
        }
        return $this;
    }
    
    public function insert()
    {
        
        if ($this->beforeSave("insert")) {
            $save = $this->buildSave("insert");
            if (count($save) > 0) {
                $sql = sprintf(
                    "INSERT IGNORE INTO `%s` SET %s;",
                    $this->getTable(),
                    join(",", $save)
                );
                if ($this->arDebug) {
                    printf("<pre>%s</pre>", $sql);
                }
                if (false !== $this->dbo->query($sql)) {
                    $this->affectedRows = $this->dbo->affectedRows();
                    if ($this->getAffectedRows() === 1) {
                        $this->insertId = $this->dbo->insertId();
                        $this->afterSave("insert");
                    }
                } else {
                    die($this->dbo->error());
                }
            }
        }
        return $this;
    }
    
    public function setBatchFields($value)
    {
        
        if (is_array($value)) {
            $this->arBatchFields = $value;
        }
        return $this;
    }
    
    public function addBatchRow($value)
    {
        
        if (is_array($value)) {
            $this->arBatch[] = $value;
        }
        return $this;
    }
    
    public function setBatchRows($value)
    {
        
        if (is_array($value)) {
            $this->arBatch = $value;
        }
        return $this;
    }
    
    private function buildBatch()
    {
       
        $save = [];
        $i = 0;
        foreach ($this->arBatch as $item) {
            foreach ($item as $k => $v) {
                $item[$k] = preg_match("/^:[a-zA-Z]{1}.*/", $v)
                    ? substr($v, 1)
                    : $this->escapeValue($v);
            }
            $save[$i] = sprintf("(%s)", join(",", $item));
            $i++;
        }
        return $save;
    }
    
    public function insertBatch()
    {
       
        if ($this->beforeSave("insertBatch")) {
            $save = $this->buildBatch();
            if (!empty($save)) {
                $sql = sprintf(
                    "INSERT IGNORE INTO `%s` (`%s`) VALUES %s;",
                    $this->getTable(),
                    join("`, `", $this->arBatchFields),
                    join(",", $save)
                );
                if ($this->arDebug) {
                    printf("<pre>%s</pre>", $sql);
                }
                if (false !== $this->dbo->query($sql)) {
                    $this->affectedRows = $this->dbo->affectedRows();
                    if ($this->getAffectedRows() > 0) {
                        $this->afterSave("insertBatch");
                    }
                } else {
                    die($this->dbo->error());
                }
            }
        }
        return $this;
    }
    
    public function join()
    {
        
        $args = func_get_args();
        switch (func_num_args()) {
            case 1:
                $this->arJoin[] = $args[0];
                return $this;
                break;
            case 2:
                $modelName = $args[0];
                $cond = $args[1];
                $type = null;
                $index = null;
                break;
            case 3:
                $modelName = $args[0];
                $cond = $args[1];
                $type = $args[2];
                $index = null;
                break;
            case 4:
                $modelName = $args[0];
                $cond = $args[1];
                $type = $args[2];
                $index = $args[3];
                break;
            default:
                throw new Exception("Number of arguments not supported.");
        }
        if (!is_null($type)) {
            $type = strtoupper(trim($type));
            if (!in_array($type, $this->joinArr)) {
                $type = "";
            } else {
                $type .= " ";
            }
        }
        if (!is_null($index)) {
            if (!preg_match("/^\s*(USE|FORCE|IGNORE)\s+(INDEX|KEY)/", $index)) {
                $index = null;
            } else {
                $index = " " . $this->escapeStr($index);
            }
        }
        if (preg_match("/([\w\.]+)([\W\s]+)(.+)/", $cond, $match)) {
            $cond = $match[1] . $match[2] . $match[3];
        }
        $className = $modelName . "Model";
        if (class_exists($className)) {
            $model = new $className();
        }
        if (isset($model) && is_object($model)) {
            $join =
                $type .
                "JOIN " .
                $model->getTable() .
                " AS t" .
                (count($this->arJoin) + 2) .
                $index .
                " ON " .
                $cond;
            $this->arJoin[] = $join;
        }
        return $this;
    }
    
    public function limit($row_count, $offset = null)
    {
       
        $this->arRowCount = (int) $row_count;
        if (!is_null($offset)) {
            $this->arOffset = (int) $offset;
        }
        return $this;
    }
    
    public function modify($data = [])
    {
        if ($this->beforeSave("modify")) {
            $data[$this->primaryKey] = $this->arData[$this->primaryKey];
            $this->setAttributes($data);
            $update = $this->buildSave();
            if (count($update) > 0) {
                $sql = sprintf(
                    "UPDATE `%s` SET %s WHERE `%s` = '%s' LIMIT 1",
                    $this->getTable(),
                    join(", ", $update),
                    $this->primaryKey,
                    $this->arData[$this->primaryKey]
                );
                if ($this->arDebug) {
                    printf("<pre>%s</pre>", $sql);
                }
                if (false !== $this->dbo->query($sql)) {
                    $this->affectedRows = $this->dbo->affectedRows();
                    if ($this->getAffectedRows() === 1) {
                        $this->afterSave("modify");
                    }
                } else {
                    die($this->dbo->error());
                }
            }
        }
        return $this;
    }
    
    public function modifyAll($data = [])
    {
       
        if ($this->beforeSave("modifyAll")) {
            $this->setAttributes($data);
            $update = $this->buildSave();
            if (count($update) > 0) {
                $sql = sprintf(
                    "UPDATE `%s` SET %s",
                    $this->getTable(),
                    join(",", $update)
                );
                $sql .= "\n";
                if (is_array($this->arWhere) && count($this->arWhere) > 0) {
                    $sql .= "WHERE " . join("\n", $this->arWhere);
                    $sql .= "\n";
                }
                if (!empty($this->arOrderBy)) {
                    $sql .= "ORDER BY " . $this->arOrderBy;
                    $sql .= "\n";
                }
                if ((int) $this->arRowCount > 0) {
                    $sql .= "LIMIT " . (int) $this->arRowCount;
                }
                if ($this->arDebug) {
                    printf("<pre>%s</pre>", $sql);
                }
                if (false !== $this->dbo->query($sql)) {
                    $this->affectedRows = $this->dbo->affectedRows();
                    $this->afterSave("modifyAll");
                } else {
                    die($this->dbo->error());
                }
            }
        }
        return $this;
    }
    
    public function offset($offset)
    {
        
        $this->arOffset = (int) $offset;
        return $this;
    }
    
    public function orderBy($order, $escape = true)
    {
        
        if ((bool) $escape === true) {
            $this->arOrderBy = $this->escapeStr($order);
        } else {
            $this->arOrderBy = $order;
        }
        return $this;
    }
    
    public function orWhere($key, $value = null, $escape = true)
    {
        
        return $this->setWhere($key, $value, "OR", $escape);
    }
    
    public function orWhereIn($key = null, $values = null)
    {
        
        return $this->setWhereIn($key, $values, false, "OR");
    }
    
    public function orWhereNotIn($key = null, $values = null)
    {
        return $this->setWhereIn($key, $values, true, "OR");
    }
    
    public function prepare($statement)
    {
        $this->statement = $statement;
        return $this;
    }
    
    public function releaseSavepoint($identifier)
    {
        
        if (
            $this->transactionStarted &&
            $this->prepare("RELEASE SAVEPOINT " . $identifier)
                ->exec()
                ->dbo->getResult()
        ) {
            return true;
        }
        return false;
    }
    
    public function reset()
    {
       
        $this->arBatch = [];
        $this->arBatchFields = [];
        $this->arData = [];
        $this->arDebug = false;
        $this->arDistinct = false;
        $this->arFrom = null;
        $this->arGroupBy = null;
        $this->arHaving = null;
        $this->arIndex = null;
        $this->arJoin = [];
        $this->arOffset = null;
        $this->arOrderBy = null;
        $this->arRowCount = null;
        $this->arSelect = [];
        $this->arWhere = [];
        $this->arWhereIn = [];
        $this->data = [];
        $this->statement = null;
        return $this;
    }
    
    public function rollback()
    {
        
        if (
            $this->transactionStarted &&
            $this->prepare("ROLLBACK")
                ->exec()
                ->dbo->getResult()
        ) {
            $this->transactionStarted = false;
            return true;
        }
        return false;
    }
    
    public function rollbackToSavepoint($identifier)
    {
        
        if (
            $this->transactionStarted &&
            $this->prepare("ROLLBACK TO SAVEPOINT " . $identifier)
                ->exec()
                ->dbo->getResult()
        ) {
            return true;
        }
        return false;
    }
    
    public function savepoint($identifier)
    {
        
        if (
            $this->transactionStarted &&
            $this->prepare("SAVEPOINT " . $identifier)
                ->exec()
                ->dbo->getResult()
        ) {
            return true;
        }
        return false;
    }
    
    public function select($fields = "*")
    {
       
        if (is_string($fields)) {
            $fields = explode(",", $fields);
        }
        foreach ($fields as $field) {
            $field = trim($field);
            if (!empty($field)) {
                $this->arSelect[] = $field;
            }
        }
        return $this;
    }
    public function set($key, $value)
    {
        foreach ($this->schema as $field) {
            if ($field["name"] == $key) {
                $this->arData[$field["name"]] = $value;
                break;
            }
        }
        return $this;
    }
    
    public function setAttributes($attr)
    {
        
        $this->arData = [];
        foreach ($this->schema as $field) {
            if (isset($attr[$field["name"]])) {
                $this->arData[$field["name"]] = $attr[$field["name"]];
            }
        }
        return $this;
    }
    
    public function setPrefix($prefix)
    {
        
        $this->prefix = $prefix;
        return $this;
    }
    
    public function setTable($tblName)
    {
       
        $this->table = $tblName;
        return $this;
    }
    
    private function setWhere(
        $key,
        $value = null,
        $type = "AND",
        $escape = true
    ) {
        
        if (!is_array($key)) {
            $key = [$key => $value];
        }
        foreach ($key as $k => $v) {
            $operator = count($this->arWhere) === 0 ? null : $type;
            if (is_null($v) && !$this->hasOperator($k)) {
                $k .= " IS NULL";
            }
            if (!is_null($v)) {
                if ($escape) {
                    $v = $this->escapeValue($v);
                }
                if (!$this->hasOperator($k)) {
                    $k .= " =";
                }
            }
            $this->arWhere[] = sprintf("%s %s %s", $operator, $k, $v);
        }
        return $this;
    }
    
    private function setWhereIn(
        $key = null,
        $values = null,
        $not = false,
        $type = "AND"
    ) {
        
        if ($key === null || $values === null) {
            return;
        }
        if (!is_array($values)) {
            $values = [$values];
        }
        $not = $not ? " NOT" : null;
        foreach ($values as $value) {
            $this->arWhereIn[] = $this->escapeValue($value);
        }
        $operator = count($this->arWhere) == 0 ? null : $type;
        $whereIn =
            $operator .
            " " .
            $key .
            $not .
            " IN (" .
            join(", ", $this->arWhereIn) .
            ") ";
        $this->arWhere[] = $whereIn;
        $this->arWhereIn = [];
        return $this;
    }
    
    public function toArray($key, $separator = "|", $newKey = null)
    {
        
        $data = $this->getData();
        foreach ($this->data as $k => $v) {
            if (is_array($v) && is_numeric($k)) {
                foreach ($v as $_k => $_v) {
                    if ($_k == $key) {
                        $_v = is_null($_v) ? "" : $_v;
                        $this->data[$k][is_null($newKey) ? $key : $newKey] =
                            strpos($_v, $separator) !== false
                                ? explode($separator, $_v)
                                : (strlen($_v) > 0
                                    ? [$_v]
                                    : []);
                        break;
                    }
                }
            } else {
                if ($k == $key) {
                    $v = is_null($v) ? "" : $v;
                    $this->data[is_null($newKey) ? $key : $newKey] =
                        strpos($v, $separator) !== false
                            ? explode($separator, $v)
                            : (strlen($v) > 0
                                ? [$v]
                                : []);
                    break;
                }
            }
        }
        return $this;
    }
    
    public function truncate($tblName = null)
    {
        
        if ($this->beforeDelete("truncate")) {
            $sql = sprintf(
                "TRUNCATE TABLE `%s`;",
                !empty($tblName) ? $tblName : $this->getTable()
            );
            if ($this->arDebug) {
                printf("<pre>%s</pre>", $sql);
            }
            if (false !== $this->dbo->query($sql)) {
                $this->afterDelete("truncate");
            } else {
                die($this->dbo->error());
            }
        }
        return $this;
    }
    
    public function validates($data)
    {
        
        foreach ($this->schema as $field) {
            if (
                isset($this->validate["rules"]) &&
                isset($this->validate["rules"][$field["name"]])
            ) {
                $rule = $this->validate["rules"][$field["name"]];
                if (is_array($rule)) {
                    foreach ($rule as $ruleName => $ruleValue) {
                        if (is_array($ruleValue)) {
                            $rule = $ruleValue;
                            array_shift($rule);
                            $param_arr = array_merge(
                                [@$data[$field["name"]]],
                                $rule
                            );
                            if (
                                !call_user_func_array(
                                    ["pjValidation", $ruleValue[0]],
                                    $param_arr
                                )
                            ) {
                                $this->errors[] = [
                                    "field" => $field["name"],
                                    "value" => @$data[$field["name"]],
                                ];
                            }
                        } else {
                            if (
                                !pjValidation::$ruleName(
                                    @$data[$field["name"]]
                                ) == $ruleValue
                            ) {
                                $this->errors[] = [
                                    "field" => $field["name"],
                                    "value" => @$data[$field["name"]],
                                ];
                            }
                        }
                    }
                } else {
                    if (!pjValidation::$rule(@$data[$field["name"]])) {
                        $this->errors[] = [
                            "field" => $field["name"],
                            "value" => @$data[$field["name"]],
                        ];
                    }
                }
            }
        }
        return count($this->errors) === 0;
    }
    
    public function where($key, $value = null, $escape = true)
    {
        
        return $this->setWhere($key, $value, "AND", $escape);
    }
    
    public function whereIn($key = null, $values = null)
    {
        
        return $this->setWhereIn($key, $values);
    }
    
    public function whereNotIn($key = null, $values = null)
    {
       
        return $this->setWhereIn($key, $values, true);
    }
    
    public function aesEncrypt($str)
    {
        
        if (!defined("PJ_USE_IV") || !PJ_USE_IV) {
            return sprintf(
                "AES_ENCRYPT(%s, %s)",
                $this->escapeValue($str),
                $this->escapeValue(PJ_SALT)
            );
        }
        return sprintf(
            "AES_ENCRYPT(%s, %s, %s)",
            $this->escapeValue($str),
            $this->escapeValue(PJ_SALT),
            $this->escapeValue(md5(PJ_SALT))
        );
    }
    
    public function aesDecrypt($column_name)
    {
        
        if (!defined("PJ_USE_IV") || !PJ_USE_IV) {
            return sprintf(
                "AES_DECRYPT(t1.%s, %s)",
                $column_name,
                $this->escapeValue(PJ_SALT)
            );
        }
        return sprintf(
            "AES_DECRYPT(t1.%s, %s, %s)",
            $column_name,
            $this->escapeValue(PJ_SALT),
            $this->escapeValue(md5(PJ_SALT))
        );
    }
}
 ?>
