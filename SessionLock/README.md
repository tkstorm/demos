##
> Session锁的问题

### 成因
- 由于会话数据被锁定以防止并发写入，所以只有一个脚本可以在任何时候进行会话操作；
- 默认情况，Session的会话数据是在脚本结束之后执行session的会话关闭，释放对应的Session锁；
- 用其他`session.save_handler`一样也有类似问题；
- 究其原因，多个连接请求，共同申请一个写锁，会出现阻塞情况；

### 解决
- 考虑在处理完所有会话数据存储设定之前，提前执行`session_write_close()`，不用等到php脚本结束在执行回收；
- 加快脚本的处理速度，降低Session锁的持有时间，避免短板效应(整个请求脚本被拖慢)；

### demo
- 正常进行，存在会话阻塞的情况：
    - 请求`http://session_lock.php`，未关闭会话，持有会话锁；
    - 请求`http://session_other.php`，出现阻塞请求；

- 提前写入会话数据和关闭会话：
    - 请求`http://../session_lock.php?close_session=1`
    - 请求`http://../session_other.ph`，未出现阻塞请求;
