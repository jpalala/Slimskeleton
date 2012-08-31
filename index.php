<?php
require 'Slim/Slim.php';
require 'models/Users.php';

$app = new Slim();

// root path
$app->get('/', function() {
    ?>
    <pre>
        <form method="post" action="/users/create">
            Username
            <input type="text" name = "username" />
            Password
            <input type="password" name = "password" />
            Password again
            <input type="password" name = "passwordagain" /><br />
            <input type="submit" name="submit" value="Register" />
        </form>
    </pre>
    <?php
});

// create user
$app->post('/users/create', function(){
    $users = new Users();
    // remove the submit element
    unset($_POST['submit']);
    if($_POST['password'] == $_POST['passwordagain'])
    {
        $filter = array('username' => '12');
        $olduser = $users->read($filter);
        if(empty($olduser))
        {
            $data = array('username' => $_POST['username'], 'password' => sha1($_POST['password']), 'passwordagain' => sha1($_POST['passwordagain']));
            $id = $users->create($data);
            echo sprintf('User created, <a href="/users/find/%s">click here</a> to view some data.', $id);
        }
        else
        {
            echo 'User already exists';
        }
    }
    else
    {
        echo 'Passwords do not match';
    }
});

// find user
$app->get('/users/find/:id', function($id) {
    $users = new Users();
    $user = $users->read(array('id' => $id));
    echo '<pre>';
    print_r($user);
    echo '</pre>';
    echo sprintf('<h2><a href="/users/delete/%s">DELETE USER</a></h2>', $id);
    ?>
    <pre>
        <form method="post" action="/users/update/<?=$user['id']?>">
            Username
            <input type="text" name = "username" value="<?=$user['username'];?>" />
            Password
            <input type="password" name = "password" value="<?=$user['username'];?>" />
            Password again
            <input type="password" name = "passwordagain" value="<?=$user['username'];?>" /><br />
            <input type="hidden" name="_METHOD" value="PUT"/>
            <input type="submit" name="submit" value="UPDATE" />
        </form>
    </pre>
    <?php
});

// update data
$app->put('/users/update/:id', function($id) {
    $users = new Users();
    unset($_POST['submit']);
    unset($_POST['_METHOD']);
    $data = $_POST;
    $data['where'] = array('id' => $id);
    $update = $users->update($data);
    if($update)
    {
        echo sprintf('User info updated, go <a href="/users/find/%s">back</a>', $id);
    }
    else
    {
        echo 'Could not update user info';
    }
});

// delete the user
$app->get('/users/delete/:id', function($id) {
    $users = new Users();
    $delete = $users->delete(array('id' => $id));
    if($delete)
    {
        echo 'User deleted, <a href="/">return to beginning</a>';
    }
    else
    {
        echo 'Delete failed';
    }
});

$app->run();