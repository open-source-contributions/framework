<?php
/**
 * @copyright Bluz PHP Team
 * @link      https://github.com/bluzphp/framework
 */

/**
 * @namespace
 */

namespace Bluz\Tests\Db;

use Bluz\Db\Query\Select;
use Bluz\Db\Query\Insert;
use Bluz\Db\Query\Update;
use Bluz\Db\Query\Delete;
use Bluz\Tests\FrameworkTestCase;
use Bluz\Proxy;

/**
 * Test class for Query Builder.
 * Generated by PHPUnit on 2013-06-17 at 13:52:01.
 *
 * @todo Separate to 4 tests for every builder
 * @todo Write tests for different DB type
 */
class QueryTest extends FrameworkTestCase
{
    /**
     * tearDown
     */
    public function tearDown()
    {
        parent::tearDown();
        Proxy\Db::delete('test')->where('email = ?', 'example@domain.com')->execute();
    }

    /**
     * Complex test of select builder
     */
    public function testSelect()
    {
        $builder = new Select();
        $builder = $builder
            ->select('u.*')
            ->addSelect('ua.*')
            ->from('users', 'u')
            ->leftJoin('u', 'users_actions', 'ua', 'ua.userId = u.id')
            ->where('u.id = ? OR u.id = ?', 4, 5)
            ->orWhere('u.id IN (?)', [4, 5])
            ->andWhere('u.status = ? OR u.status = ?', 'active', 'pending')
            ->orWhere('u.login LIKE (?)', 'A%')
            ->orderBy('u.id')
            ->addOrderBy('u.login')
            ->limit(5);

        $check = 'SELECT u.*, ua.*'
            . ' FROM users u LEFT JOIN users_actions ua ON ua.userId = u.id'
            . ' WHERE (((u.id = "4" OR u.id = "5") OR (u.id IN ("4","5")))'
            . ' AND (u.status = "active" OR u.status = "pending")) OR (u.login LIKE ("A%"))'
            . ' ORDER BY u.id ASC, u.login ASC'
            . ' LIMIT 5 OFFSET 0';

        self::assertEquals($builder->getQuery(), $check);
    }

    /**
     * Complex test of select builder
     */
    public function testSelectWithGroupAndHaving()
    {
        $builder = new Select();
        $builder = $builder
            ->select('p.*')
            ->from('pages', 'p')
            ->groupBy('p.userId')
            ->addGroupBy('MONTH(p.created)')
            ->having('MONTH(p.created) = :month1')
            ->orHaving('MONTH(p.created) = :month2')
            ->andHaving('p.userId <> 0')
            ->setParameters([':month1' => 2, ':month2' => 4]);;

        $check = 'SELECT p.*'
            . ' FROM pages p'
            . ' GROUP BY p.userId, MONTH(p.created)'
            . ' HAVING ((MONTH(p.created) = :month1) OR (MONTH(p.created) = :month2)) AND (p.userId <> 0)';

        self::assertEquals(2, $builder->getParameter(':month1'));
        self::assertEquals(4, $builder->getParameter(':month2'));
        self::assertEquals($builder->getQuery(), $check);
    }

    /**
     * Complex test of select builder
     */
    public function testSelectWithInnerJoin()
    {
        $builder = new Select();
        $builder = $builder
            ->select('u.*', 'p.*')
            ->from('users', 'u')
            ->join('u', 'pages', 'p', 'p.userId = u.id');

        $check = 'SELECT u.*, p.*'
            . ' FROM users u INNER JOIN pages p ON p.userId = u.id';

        self::assertEquals($builder->getQuery(), $check);
    }

    /**
     * Complex test of select builder
     */
    public function testSelectWithRightJoin()
    {
        $builder = new Select();
        $builder = $builder
            ->select('u.*', 'p.*')
            ->from('users', 'u')
            ->rightJoin('u', 'pages', 'p', 'p.userId = u.id');

        $check = 'SELECT u.*, p.*'
            . ' FROM users u RIGHT JOIN pages p ON p.userId = u.id';

        self::assertEquals($builder->getQuery(), $check);
    }

    /**
     * Complex test of select builder
     */
    public function testSelectToStringConversion()
    {
        $builder = new Select();
        $builder = $builder
            ->select('u.*', 'p.*')
            ->from('users', 'u')
            ->join('u', 'pages', 'p', 'p.userId = u.id')
            ->where('u.id = ? OR u.id = ?', 4, 5);

        $check = 'SELECT u.*, p.*'
            . ' FROM users u INNER JOIN pages p ON p.userId = u.id'
            . ' WHERE u.id = ? OR u.id = ?';

        self::assertEquals($check, (string)$builder);
    }

    /**
     * Complex test of insert builder
     */
    public function testInsert()
    {
        $builder = new Insert();
        $builder = $builder
            ->insert('test')
            ->set('name', 'example')
            ->set('email', 'example@domain.com');
        $check = 'INSERT INTO `test` SET `name` = "example", `email` = "example@domain.com"';

        self::assertEquals($builder->getQuery(), $check);
        self::assertGreaterThan(0, $builder->execute());
    }

    /**
     * Complex test of update builder
     */
    public function testUpdate()
    {
        $builder = new Update();
        $builder = $builder
            ->update('test')
            ->setArray(
                [
                    'status' => 'disable'
                ]
            )
            ->where('email = ?', 'example@domain.com');
        $check = 'UPDATE `test` SET `status` = "disable" WHERE email = "example@domain.com"';

        self::assertEquals($builder->getQuery(), $check);
        self::assertEquals(0, $builder->execute());
    }

    /**
     * Complex test of delete builder
     */
    public function testDelete()
    {
        $builder = new Delete();
        $builder = $builder
            ->delete('test')
            ->where('email = ?', 'example@domain.com')
            ->limit(1);
        $check = 'DELETE FROM `test` WHERE email = "example@domain.com" LIMIT 1';

        self::assertEquals($builder->getQuery(), $check);
        self::assertEquals(0, $builder->execute());
    }
}
