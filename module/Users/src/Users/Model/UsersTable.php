<?php
namespace Users\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Container;

class UsersTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function saveUser(Users $user)
    {
        $role = 'user';
        $data = [
            'name' => $user->name,
            'login' => $user->login,
            'password' => $user->password,
            'email' => $user->email,
            'role' => $role
        ];

        $id = (int) $user->id;

        if (!$id) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getUser($id)) {
                $this->tableGateway->update($data, ['id' => $id]);
            } else {
                throw new \Exception('Users ID does not exists');
            }
        }
    }

    public function getUser($id) {
        $id = (int) $id;

        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception('Could not find Id');
        }
        return $row;
    }

    public function getUserByLogin($login)
    {
        $rowset = $this->tableGateway->select(['login' => $login]);
        $row = $rowset->current();
        if (!$row) {
            throw \Exception('Could not find login');
        }
        return $row;
    }

    public function deleteUser($id)
    {
        $id = (int) $id;
        $this->tableGateway->delete(['id' => $id]);
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }
}