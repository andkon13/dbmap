<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 09.07.15
 * Time: 18:13
 */

/**
 * Class ModelTest
 */
class ModelTest extends \tests\Base
{
    static private $revertTable = true;

    public function testCreate()
    {
        $model = new \dbmap\User();
        $res   = $model->save();
        if ($res) {
            throw new PHPUnit_Framework_Exception('error validator');
        } elseif (!$model->hasErrors()) {
            throw new PHPUnit_Framework_Exception('error hasErrors');
        }

        $model->name = 'test';
        $res         = $model->save();
        if (!$res) {
            throw new PHPUnit_Framework_Exception('error save');
        } elseif (empty($model->created)) {
            throw new PHPUnit_Framework_Exception('error set created');
        }
    }

    public function testFindAll()
    {
        $models = \dbmap\User::findAll();
        if (!is_array($models)) {
            throw new PHPUnit_Framework_Exception('error findAll. result must by array');
        } elseif (!$models[0] instanceof \dbmap\User) {
            throw new PHPUnit_Framework_Exception('error findAll. items must by User');
        }
    }

    public function testFindBySql()
    {
        $models = \dbmap\User::findBySql(
            'select * from ' . \dbmap\User::getTableName() . ' where status = :st',
            [':st' => 0]
        );
        if (!is_array($models)) {
            throw new PHPUnit_Framework_Exception('error findBySql. result must by array');
        } elseif (!$models[0] instanceof \dbmap\User) {
            throw new PHPUnit_Framework_Exception('error findBySql. items must by User');
        }
    }

    public function testFindById()
    {
        $model = \dbmap\User::findById(1);
        if (!$model) {
            throw new PHPUnit_Framework_Exception('user.id = 1 not found');
        } elseif (!$model instanceof \dbmap\User) {
            throw new PHPUnit_Framework_Exception('model myst by User');
        }
    }

    public function testFundByName()
    {
        $model = \dbmap\User::findByName('test');
        if (!$model) {
            throw new PHPUnit_Framework_Exception('user.name = test not found');
        } elseif (!$model instanceof \dbmap\User) {
            throw new PHPUnit_Framework_Exception('model myst by User');
        }
    }

    public function testUpdate()
    {
        /** @var \dbmap\User $model */
        $model         = \dbmap\User::findById(1);
        $model->status = 1;
        $res           = $model->save();
        if (!$res) {
            throw new PHPUnit_Framework_Exception('update error');
        } elseif ($model->status != 1) {
            throw new PHPUnit_Framework_Exception('status not updated');
        } elseif (empty($model->updated)) {
            throw new PHPUnit_Framework_Exception('updated not sets');
        }
    }

    public function testDelete()
    {
        /** @var \dbmap\User $model */
        $model = \dbmap\User::findById(1);
        $res   = $model->delete();
        if (!$res) {
            throw new PHPUnit_Framework_Exception('delete error');
        }

        $model = \dbmap\User::findById(1);
        if ($model) {
            throw new PHPUnit_Framework_Exception('model not deleted');
        }
    }

    protected function setUp()
    {
        parent::setUp();
        if (self::$revertTable) {
            $sql = '
                START TRANSACTION ;
                SET FOREIGN_KEY_CHECKS = 0;
                TRUNCATE user;
                TRUNCATE client;
                SET FOREIGN_KEY_CHECKS = 1;
                COMMIT ;
            ';
            \dbmap\base\Pdo::getInstance()->execute($sql);
            self::$revertTable = false;
        }
    }
}
