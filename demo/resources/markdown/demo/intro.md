# 复杂多轮对话问题



CommuneChatbot 项目最大的特点是可以实现复杂的多轮对话, 然而这个领域的技术问题目前似乎讨论的还不够多, 您可以通过本文了解作者的思路.

## 1. 什么是复杂多轮对话

作者将复杂多轮对话问题称之为 N阶多轮对话问题, 简单来说有三个阶段:

* 单轮对话 (0阶多轮对话)
* 1阶多轮对话
* N阶多轮对话

### 1.1 单轮对话

如果一问一答就完成了一次对话任务, 就是单轮对话. 例如:

    用户: 长沙明天的气温如何?
    机器人: 长沙明天气温 6 ~ 20 度, 早晚请注意添加衣物.

许多闲聊机器人, 并没有实现上下文联系, 就是典型的单轮对话. 最基本的问答式机器人, 也是单轮对话. 事实上, 命令行与搜索框, 都可以理解成一种单轮对话交互.

闲聊机器人技术上可以非常复杂, 要能从成千上万的语料中训练出能应答各种问题的机器人, 但工程上反而很简单, 本质上就是一个知识库的查询.

### 1.2 1阶多轮对话

对话机器人需要达成一个任务时, 往往无法一次获得所有参数信息, 而需要引导用户进行多个单轮的对话. 例如:

    用户: 我想查询天气
    机器人: 您好, 请问您想了解哪个城市的天气?
    用户: 长沙的   // 获取城市参数
    机器人: 那请问您想了解哪天的天气呢?
    用户: 后天的   // 获取时间参数
    机器人: 好的, 后天长沙的天气是..... // 执行查询逻辑并反馈

这就构成了一个线性的多轮对话. 可能需要 N 轮对话完成一个任务. 与单轮对话相比, 它又衍生出额外的技术问题:

__语义作用域__ : 如果单轮对话能响应 100 种用户意图, 它每一轮对话都会试图匹配这 100 种意图, 称之为 ```开放域``` 匹配. 然而进入到多轮对话时, 机器人需要的就是 "长沙" 这个信息, 那么用户别的意图就应该 ```拒答``` , 于是语义的作用域就变窄了, 变成```封闭域```.

__上下文记忆__ : 单轮对话不需要记忆. 而多轮对话, 则前几轮用户提供的信息, 例如 "城市" 或者 "日期" 需要带到最后一步. 这就需要至少在本次多轮对话中携带上下文记忆.

__退出对话__ : 由于对话有多个轮次, 用户就可以在某一轮产生退出对话的意愿. 这时机器人就要退出任务并给予反馈 (例如 "好的, 欢迎下次再来").

现阶段大部分多轮对话机器人, 都做到了实现 1阶多轮对话.

### 1.3 N阶多轮对话

现实中的任务, 往往不是简单的线性结构的, 而是可以分解成若干个子任务, 相互嵌套而成. 每一个子任务都可以分解成一个 1阶的多轮对话. 而子任务又可以拆解成新的子任务, 这就形成了1阶多轮对话的嵌套, 产生了像分叉树一样的对话结构 :

    机器人: 您好, 请问我有什么可以帮您? // 基础对话
    用户: 我想买果汁
        机器人: 好的, 请问您想要什么口味的? // 开启购物多轮对话
        用户: 我想要苹果汁, 常温哈
        机器人: 好的, 请问是杯装还是碗装 ?
        用户: 杯装就可以.
            机器人: 对了, 您办理我家会员可以享受8折优惠哦 // 会员卡导购
            用户: 不用了 // 退出导购语境
        机器人: 好的, 请付15元.  // 回到购物的多轮对话
        用户: 这么贵啊? 我不要了
        机器人: 好的, 您的订单已取消  // 取消会话
    机器人: 您好, 请问还有什么可以帮您?  // 回到基础对话
    用户: 我想买电影票
    ...

在这个简单的例子中, 可以看到 :

* __对话嵌套__ : 一个多轮对话内部, 某个节点可以是另一个多轮对话, 形成嵌套
* __对话分支__ : 根据条件不同, 对话会走向不同的子多轮对话分支.
* __对话循环__ : 一些多轮对话可以反复循环, 直到用户要求退出.
* __无限轮次__ : N阶多轮对话可以无限轮次


## 2. N阶多轮对话的复杂性

N 阶多轮对话本质上可以反复分割, 就像是分形几何的图形一样. 因为子对话的存在, 于是产生了 ```分支``` 和 ```循环``` . 整体看起来像一个树状结构.

如果子对话的结果返回到父对话, 将决定父对话的下一步怎么走, 这就不是树状结构, 而是图状结构了.

这种图状结构, 并非一阶多轮对话的简单嵌套, 它带来了更多的复杂性. 在 CommuneChatbot 项目中, 总结了以下几个方面:

* 半开放域语境
* 语境的跳转与回归
* 返回上一步
* 逃离语境与拦截
* 有作用域的上下文记忆
* 多任务调度
* 双工下的状态管理: 让出, 异步与抢占

### 2.1 半开放域语境

常见的基于填槽实现的多轮对话机器人, 往往在匹配意图时是完全开放域的 (可以匹配所有意图), 而在填写实体参数时变成完全封闭域的 (不再匹配任何意图).

而现实对话中, 语境往往是半开放域的. 每一种语境都可能允许匹配部分意图, 然后拒答其它. 这有点像页面式交互的 app, 每一页都是一个半开放域语境, 都有若干可以执行的意图 (点击的按钮).

实现 N 阶多轮对话, 首先就要做到定义半开放域的语境, 并保证它们之间相互隔离. 每个半开放域语境相当于完成一个独立任务的一阶多轮对话, 在运行中可以嵌套或循环其它语境.

### 2.2 语境的跳转与回归

既然有了多个半开放域的语境, 就存在语境的跳转, 以及回归. 回归的形式决定了跳转的形式.

CommuneChatbot 定义了以下三组基本的跳转和回归:

* 依赖 : dependOn & intended, A 语境依赖 B 语境的结果
* 挂起 : sleep & fallback, A 语境不依赖 B 语境的结果, 但当 B 结束了会唤醒 A
* 替代 : replace, A 将自己替换成 B , 自己从上下文中消失

### 2.3 返回上一步

当我们说话时口误, 我们常常立刻纠正口误信息, 想返回上一步.

让语境返回上一步,  这对于人类很容易做到, 但对于对话机器人而言就不简单了. 因为对话的状态发生了变更, 一些副作用 (计算导致的参数变化) 也发生了. 因此存在三种可能性

* 完全不可回溯
* 对话可回溯, 副作用不可回溯
* 对话可回溯, 副作用也可以消除.

现阶段大多数对话机器人, 都无法返回上一步. CommuneChatbot 则可以选择保留几个```快照(snapshot)``` , 从而可以返回几步. 但已经发生的副作用难以完全消除. 这种回退的机制很像浏览器的```返回```.

### 2.4 逃离语境与拦截

在多轮对话流程中, 有种种原因可能导致流程突然中断, 例如:

* cancel : 用户主动放弃
* reject : 用户无权限
* failure : 服务端发生错误

这些情况发生如果没有处理, 对话就会陷入死循环. 因此是需要 "逃离" 当前语境的. 问题在于, 流程中断后, 对话应该回归到哪一个节点呢?

* 退回上一步?
* 彻底退出整个对话?
* 退回某一步

容易想到, 退回上一步很可能无法解决问题. 而彻底退出整个对话, 对于长程多轮对话而言极其不友好.

CommuneChatbot 的方案是, ```A => B => ... => N``` 这样嵌套很多层的语境跳转, 可以根据是否依赖跳转语境的执行结果, 拆分成若干个 Tread :

    [thread1 : A => B => C] => [thread2 : D => E ] => [thread n: X => ... => N]

每个 Thread 内部的语境是相互依赖的, 而 Thread 之间没有依赖.

这样当 Thread 当前节点发生逃离语境事件时, 整个 Thread 都会被退出. 而返回到另一个 Thread. 当没有上级 Thread 存在时, 整个会话才退出.

进一步的, 当 cancel , reject 这些逃离事件发生时, 它们会像 HTML 的 DOM 树事件那样, 逐层往上冒泡. 每一层都可以定义自己的拦截方法, 终止正常的退出逻辑.


### 2.5 有作用域的上下文记忆

多轮对话管理一定要实现上下文记忆. 然而记忆也会有短程和长程的. 比如 ```问用户的名字```, 就应该永远都记得.

通常的对话机器人项目, 存在短程和长程记忆. 长程就是无限期存储的; 而短程只在一个 session 的生命周期中生效.

而 CommuneChatbot 中自带的记忆体, 可以自行定义作用域, 类似于局部变量. 只要在作用域一致的情况下, 拿出来永远是同一份记忆.

例如问用户 ```张三每周三下午两点有什么课程``` , 得到的信息可以存储在作用域为 ```人:张三; 每周:三; 时间:下午两点``` 这三个维度定义的作用域中. 只要查询的作用域与之相同, 得到的永远是相同的数据.


### 2.6 多任务调度

目前的多轮对话机器人很少考虑多任务调度的问题. 在 CommuneChatbot 中定义了 Thread, 定义了 dependOn 和 Sleep 机制, 并且能保证上下文记忆, 因此可以实现多任务调度.

例如官网上的例子:

    用户: 我想买水果汁
    机器人: 请问您需要什么口味的?
    用户: 我想要苹果口味的
    机器人: 请问是否要加冰
        用户: 长沙明天天气怎么样?  // 跳转到另一个任务, 当前任务挂起 (sleep)
        机器人: 长沙明天的... 还有其它问题吗?
        用户: 没有了
    机器人: 请问是否要加冰 // 跳转回到买果汁 (fallback)

这是一个被动匹配, 使用户从 任务A 跳转到 任务B , 又能够调度回来的例子.

在这套机制基础上可以实现多任务调度, 每个任务就是一个 Thread, 用户可以选择让哪一个 Thread 控制当前会话, 而其它 Thread 进入 sleep 状态, 等待未来跳转回来 (fallback), 或者主动唤醒 (wake).

### 2.7 双工下的状态管理: 让出, 异步与抢占

一般的双工指的是通信上的互通. 但对于对话系统而言, 双工不仅是可以主动推送信息给用户, 还意味着上下文语境也可能在机器人方主导下变化:

    用户: 帮我搜索一下张三的资料  // 用户发布搜索任务
    机器人: 好的, 搜索中
    机器人: 稍等, 您有一个电话过来的, 您需要现在接听吗?   // 机器人端主动打断流程
    用户: 好, 我先接电话 // 用户的回复与上一个任务无关

通常对话的语境切换都是由用户单方面主导. 而双工的通信, 导致了机器人方也能主导语境切换. 两者就必须解决冲突的可能性.

由于机器人一方, 很可能是从第三方服务接受到信号, 才主动变更语境的; 因此实现 ```半双工``` 还不行, 很可能用户和第三方服务在同一个瞬间发来消息, 导致第三方服务的信息被丢弃.

更重要的是, 用户在对话过程中, 自己脑海里也会维护一个对话的状态; 对话机器人的状态在双工场景中收到其它因素改变, 也必须保证和用户的理解同步, 否则就会进入鸡同鸭讲的死循环中.

CommuneChatbot 为此设计了一整套方案 (目前版本尚未实装) . 简单而言, 正常的上下文切换中, Thread 有一个 ```sleeping栈```. ```sleeping栈``` 是用户方可以主导的.


而双工的场景中, 额外增加 ```yielding栈``` 和 ```blocking``` 栈. 只能由机器方主导.

当一个 Thread 主动让出会话的控制权, 等待异步返回的结果时, 就进入 ```yielding``` 栈.

当一个 ```yielding``` 状态的 Thread 得到异步回调唤醒后, 或者第三方服务唤起了一个新的 Thread, 它们可以进入 ```sleeping栈```, 或者选择进入 ```blocking栈```.

进入 ```blocking栈``` 的对话, 可以通过双工通道主动向用户推送消息. 但只有在用户下一次回复到达的时候, ```blocking栈``` 中的语境才会```抢占``` 控制权, 把当前的会话压入 ```sleeping栈``` 等待唤醒.

用这种策略, 对于用户方而言, 自己的话可能因为 ```抢占```的关系, 被机器人拒答, 而引入另一个语境. 而对于第三方服务, 是允许随时回调的.

这套技术方案是否可行, 还需要现实的双工通道和有异步的业务场景共同来验证.


## 3. CommuneChatbot 的多轮对话工程思路

__多轮对话的本质是多轮交互__ : 交互就是人与机器之间用各种形式传递信息. 从交互的角度来看, 浏览器, app, 桌面软件, 和多轮对话机器人并没有本质的区别.

__用编程语言对多轮对话建模__ : 用工程化的方式实现 N阶多轮对话机器人, 本质上就是用编程语言对多轮交互建模. 这其实和用 MVC 框架实现网站, 用 vue 或 react 实现一个网页应用, 也没有本质区别.

按这样的思路, 可以对比其它多轮交互应用的 features, 定义出对话交互所需要的 features. 而实现思路也是类似的.

* 一阶多轮对话 : 类似于 function, 是对过程的封装
* 语境的跳转与回归 : 类似于 function 调用另一个 function, 有时依赖 return, 有时不依赖
* 逃离语境与拦截 : 本质上是一个 try ... catch ... 机制
* 有作用域的上下文记忆 : 相当于编程语言有作用域的局部变量.
* 挂起, 异步与抢占 : 可参考协程模式, 非常相似
* 多任务调度 : 参考多线程与 IO

编程语言通常有自己的调用栈来实现语法的执行. CommuneChatbot 也有自己的```history``` 栈记录状态.

与本地程序最大的区别在于, __服务端的对话机器人__ 必须要实现 __可分布式部署__, 从而必须实现分布式一致性. 会话所有的记忆应当存在保证分布式一致性的介质中. 因为若每个 session 整个生命周期都维护在单一实例上, 而每一轮对话相隔若干秒, 这会造成巨大的资源浪费.

进一步的, 对于无限轮次的多轮对话而言, 并非所有中间节点都是可回溯的. 不可回溯的节点, 和上下文记忆就应该及时回收, 释放资源. 这就像编程语言的 GC 了.


__复杂多轮对话__ 是一个复杂的工程问题. CommuneChatbot 项目只是做了初步的探索. 更多的功能点和实现手段, 还需要在具体业务场景推动下逐步完善.