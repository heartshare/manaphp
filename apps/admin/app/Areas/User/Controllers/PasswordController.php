<?php
namespace App\Areas\User\Controllers;

use App\Models\Admin;
use ManaPHP\Mvc\Controller;

class PasswordController extends Controller
{
    public function getAcl()
    {
        return ['*' => 'user', 'reset' => '*', 'forget' => '*', 'captcha' => '*'];
    }

    public function captchaAction()
    {
        return $this->captcha->generate();
    }

    public function forgetAction()
    {
        if ($this->request->isPost()) {
            $admin_name = input('admin_name');
            $email = input('email');

            $admin = Admin::first(['admin_name' => $admin_name]);
            if (!$admin || $admin->email !== $email) {
                return $this->response->setJsonError('账号不存在或账号与邮箱不匹配');
            }
            return $this->response->setJsonData(jwt_encode(['admin_name' => $admin_name, 'scope' => 'admin.user.password.forget'], 300), '重置密码链接已发到您的邮箱');
        } else {
            $this->view->setVar('redirect', input('redirect', $this->router->createUrl('/')));

            return $this->view->setVar('admin_name', $this->cookies->get('admin_name'));
        }
    }

    public function resetAction()
    {
        if ($this->request->isAjax()) {
            $claims = jwt_decode(input('token'), 'admin.user.password.forget');

            $admin_name = $claims['admin_name'];

            $admin = Admin::firstOrFail(['admin_name' => $admin_name]);
            $admin->password = input('password');
            $admin->update();

            return $this->response->setJsonData([], '重置密码成功');
        } else {
            $claims = jwt_decode(input('token'), 'admin.user.password.forget');
        }
    }

    public function changeAction()
    {
        if ($this->request->isPost()) {
            $admin = Admin::get($this->identity->getId());
            if (!$admin->verifyPassword(input('old_password'))) {
                return '旧密码不正确';
            }

            $admin->password = input('new_password');
            if (input('new_password_confirm') !== $admin->password) {
                return '两次输入的密码不一致';
            }

            $admin->update();
            $this->session->destroy();
            return 0;
        }
    }
}