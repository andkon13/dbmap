<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 11.04.14
 * Time: 11:28
 */

namespace dbmap\base;

/**
 * Class Dummy
 *
 * @package dbmap\base
 */
trait Dummy
{
    /**
     * Действие перед валидацией
     *
     * @return bool
     */
    public function beforeValidate()
    {
        return true;
    }

    /**
     * Действие после сохранения
     *
     * @return void
     */
    public function afterSave()
    {
    }

    /**
     * Действие перед удалением
     *
     * @return bool
     */
    public function beforeDelete()
    {
        return true;
    }

    /**
     * Действие после удаления
     *
     * @return void
     */
    public function afterDelete()
    {
    }

    /**
     * Действие перед сохранением
     *
     * @return bool
     */
    private function beforeSave()
    {
        return true;
    }
}
