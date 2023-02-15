<?php

namespace Home\Controller;
use Think\Controller;
use PhpMyAdmin\SqlParser\Components\Condition;

class firstController extends Controller
{
    public function register()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $name = $data->name;
        $surname = $data->surname;
        $email = $data->email;
        $password = $data->password;

        if (
            is_null($name) ||
            strlen($name) < 1 ||
            is_null($surname) ||
            strlen($surname) < 1 ||
            is_null($email) ||
            strlen($email) < 1
        ) {
            response(false, "Do not leave empty ", false);
        }

        if (is_null($password)) {
            response(false, "Please enter password", false);
        }
        if (strlen($password) < 7) {
            response(
                false,
                "Password can not be smaller than 7 characters",
                false
            );
        }
        if (
            preg_match("/[A-Z]/", $password) == false ||
            preg_match("#[0-9]#", $password) == false
        ) {
            response(
                false,
                "Password must include at least one uppercase letter and at least one number",
                false
            );
        }

        $check = M("user")
            ->where(["email" => $email])
            ->find();

        if (count($check) > 0) {
            response(false, "email already exists", false);
        }

        $user = M("user");

        $datalist[] = [
            "name" => $name,
            "surname" => $surname,
            "email" => $email,
            "password" => md5($password),
        ];

        $user->addAll($datalist);
        response(true, "Registration Completed!", true);
    }

    public function login()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $email = $data->email;
        $password = $data->password;

        $check = M("user")
            ->where(["email" => $email, "password" => md5($password)])
            ->select();

        // var_dump($check);
        // die();

        if (count($check) == 0) {
            response(true, "Email or password is wrong", true);
        }
        Session("ID", $check[0]["id"]);
        var_dump(session("ID"));
        response(true, "Logged in successfuly", true);
    }

    public function change_password()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $email = $data->email;
        $old_password = $data->old_password;
        $new_password = $data->new_password;

        $check = M("user")
            ->where(["email" => $email, "password" => md5($old_password)])
            ->select();

        // var_dump($check);
        // die();

        if (count($check) == 1 && $old_password == $new_password) {
            response(
                false,
                "new password cannot be same as old password",
                false
            );
        }
        if (strlen($new_password) < 7) {
            response(
                false,
                "Password can not be smaller than 7 characters",
                false
            );
        }
        if (
            preg_match("/[A-Z]/", $new_password) == false ||
            preg_match("#[0-9]#", $new_password) == false
        ) {
            response(
                false,
                "Password must include at least one uppercase letter and at least one number",
                false
            );
        } elseif (count($check) == 1) {
            $user = M("user");
            $user
                ->where(["email" => $email])
                ->setField("password", md5($new_password));

            response(true, "Password changed", true);
        }
        response(false, "email or password wrong", false);
    }

    public function delete_my_account()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $name = $data->name;
        $surname = $data->surname;
        $email = $data->email;
        $password = $data->password;

        $check = M("user")
            ->where([
                "email" => $email,
                "name" => $name,
                "surname" => $surname,
                "password" => md5($password),
            ])
            ->select();

        if (count($check) == 0) {
            response(false, "user not found", false);
        }

        $person = M("user");
        $person
            ->where([
                "email" => $email,
                "name" => $name,
                "surname" => $surname,
                "password" => md5($password),
            ])
            ->delete();

        response(true, "user deleted", true);
    }

    public function add_to_list()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $todo = $data->todo;
        $date = $data->date;
        $time = $data->time;
        $title = $data->title;

        $user_id = session("ID");

        $datalist[] = [
            "user_id" => $user_id,
            "todo" => $todo,
            "date" => $date,
            "time" => $time,
            "title" => $title,
        ];

        $item = M("todos")->addAll($datalist);
        response(true, "Item added!", true);
    }

    public function checkbox()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $done = $data->done;
        $title = $data->title;
        $date = $data->date;
        $check = 1;

        if (is_null($done)) {
            echo "";
        } else {
            $item = M("todos");
            $item
                ->where(["title" => $title, "date" => $date])
                ->setField("done", $check);

            response(true, "Task done.", true);
        }
    }

    public function see_my_list()
    {
        $user_id = session("ID");

        $item = M("todos")
            ->field(["id", "todo", "date", "time"])
            ->where(["user_id" => $user_id])
            ->select();

        response($item, "your list", true);
    }

    public function update()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $task_id = $data->task_id;
        $title = $data->title;
        $todo = $data->todo;
        $date = $data->date;
        $time = $data->time;

        if (isset($title)) {
            $item = M("todos");
            $item
                ->where(["id" => $task_id, "user_id" => session("ID")])
                ->setField("title", $title);
            response(true, "Title is set", true);
        }

        if (isset($todo)) {
            $item = M("todos");
            $item
                ->where(["id" => $task_id, "user_id" => session("ID")])
                ->setField("todo", $todo);
            response(true, "Task is set", true);
        }
        if (isset($date)) {
            $item = M("todos");
            $item
                ->where(["id" => $task_id, "user_id" => session("ID")])
                ->setField("date", $date);
            response(true, "Date is set", true);
        }
        if (isset($time)) {
            $item = M("todos");
            $item
                ->where(["id" => $task_id, "user_id" => session("ID")])
                ->setField("time", $time);
            response(true, "Time is set", true);
        }
    }

    public function logout()
    {
        session("ID", null);

        echo "Logged Out";
    }
}
