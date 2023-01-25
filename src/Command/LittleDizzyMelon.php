<?php

declare(strict_types=1);
/**
 * This file is part of zhuchunshu.
 * @link     https://github.com/zhuchunshu
 * @document https://github.com/zhuchunshu/super-forum
 * @contact  laravel@88.com
 * @license  https://github.com/zhuchunshu/super-forum/blob/master/LICENSE
 */
namespace App\Plugins\LittleDizzyMelon\src\Command;

use App\Model\AdminUser;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use HyperfExt\Hashing\Hash;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * @Command
 */
#[Command]
class LittleDizzyMelon extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('plugin:forgot-admin-password');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Little Dizzy Melon：小晕瓜后台密码找回插件');
    }

    public function handle()
    {
        $admin_users = [];
        foreach (AdminUser::get(['username', 'email', 'created_at']) as $item) {
            $admin_users[] = [
                $item->username,
                $item->email,
                $item->created_at,
            ];
        }
        $table = new Table($this->output);
        $table
            ->setHeaders(['用户名', '邮箱', '创建时间'])
            ->setHeaderTitle('账号列表')
            ->setFooterTitle('共: ' . AdminUser::count() . ' 条结果')
            ->setRows($admin_users);
        $table->render();
        $email = $this->ask('请输入要重置密码的管理员邮箱账号');
        if (! AdminUser::where('email', $email)->exists()) {
            $this->error('邮箱为: ' . $email . ' 的管理员账号不存在');
            return;
        }
        $pwd = $this->ask('请输入新的密码');
        $result = AdminUser::where('email', $email)->update([
            'password' => Hash::make($pwd),
        ]);
        if (! $result) {
            $this->error('密码重置失败!');
            return;
        }
        $this->info('密码重置成功!');
    }
}
