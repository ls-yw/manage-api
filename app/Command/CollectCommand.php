<?php
declare(strict_types = 1);

namespace App\Command;

use App\Constants\RedisKeyConstant;
use App\Services\Novel\AppCollectService;
use App\Services\Novel\BookService;
use App\Services\Novel\CollectService;
use App\Utils\Redis\Redis;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

/**
 * @Command
 */
class CollectCommand  extends HyperfCommand
{
    /**
     * 执行的命令行
     *
     * @var string
     */
    protected $name = 'collect';

    public function handle()
    {
        $lockKey = RedisKeyConstant::TASK_LOCK.'collect';
        if (Redis::getInstance()->exists($lockKey)) {
            return;
        }

        Redis::getInstance()->setEx($lockKey, 3600, 1);

        try{
            // 通过内置方法 line 在 Console 输出 Hello Hyperf.
            $pageKey = RedisKeyConstant::TASK_AUTO_COLLECT_PAGE;
            $page = !Redis::getInstance()->exists($pageKey) ? 1 : (int)Redis::getInstance()->get($pageKey);
            $books = (new BookService())->getList('', '', 1, $page, 50);
            if (empty($books)) {
                echo '第'.$page.'页 无待采集的小说'.PHP_EOL;
                Redis::getInstance()->del($pageKey);
                return;
            }
            foreach ($books as $book) {
                $collect = (new CollectService())->getById($book->collect_id);
                if (empty($collect)) {
                    echo '采集规则不存在，collectId:'.$book->collect_id.PHP_EOL;
                    return;
                }
                if (2 === $collect->target_type) {
                    (new AppCollectService())->startCollect($book->id);
                }
            }
        }catch (\Exception $e) {
            echo '抛出了错误：'.$e->getMessage();
        }

        Redis::getInstance()->setEx($pageKey, 86400, $page+1);
        Redis::getInstance()->del($lockKey);
    }
}