<?php
use PrimitivePlus\Entity;

class User extends Entity {
    static function schema() {
        return array(
            'name' => '',
            'email' => array(self::STR),
        );
    }

    function checkErrors() { return array(); }

    static function cast(self $user) {
        return parent::_cast($user);
    }
}

class Administrator extends User {
    function adminMethod() {
        return 'adminMethod!';
    }
}

class Viewer extends User {
    function viewerMethod() {
        return 'viewerMethod!';
    }
}

class EntityTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function テストケース() {
        $user = new User;
        $user->name = 'taro';
        $user->email = 'taro@example.com';

        $admin = Administrator::cast($user);
        $admin->name = 'hanako';

        $this->assertEquals('hanako', $user->name);
        $this->assertEquals('adminMethod!', $admin->adminMethod());

        $viewer = Viewer::cast($user);
        $this->assertEquals('viewerMethod!', $viewer->viewerMethod());
    }
}
