# Ghost In Shells

[//]: # (@stageName ghost_in_shells)
[//]: # (@stageDesc 多个设备同构为一个机器人)

CommuneChatbot 作为对话式交互系统，旨在把多个设备、多个对话平台同构为一个对话机器人。这是 v0.2 版最大的改动之一，为此我舍弃了原有代码，重新写了十几万行代码以实现它。

[//]: # (@info)

这个方案说起来拗口，我个人取了个好玩的名字，叫 ```Ghost in Shells``` 架构。

[//]: # (@info)

其实就是常见的分布式系统思路：把多个对话平台、可用对话操作的设备接入到同一个服务端内核上，原来单一对话系统的"多轮对话管理内核" 就变成公共的 "状态与行为管理中控"（Ghost)。

[//]: # (@askChoose)
[//]: # (@routeToRelation children)

## Ghost 与 Shell 的分工

[//]: # (@stageName usages_of_ghost_or_shell)

Ghost 从所有接入设备中获得输入信息，然后把决策信息原路返回，或者广播给所有设备。从而实现同构。

[//]: # (@info)

而每个设备的输入输出、通信机制（单通、双工、异步等）、消息的模态（文本、语音、视频）都不一样。

[//]: # (@info)

于是需要一个 "shell" 中间层，负责不同设备特异性的通讯、服务，将输入信息一致化提交给 Ghost，再将 Ghost 上一致化的输出渲染成平台自己的消息。

[//]: # (@info)

这个技术方案的长远目标显然是搭建全栈的机器人，而 CommuneChatbot 现阶段只迈出了小小的一步，重点实现对话机器人层面跨设备的同构，比如用微信公众号操作网页版。


[//]: # (@askChoose)
[//]: # (@routeToRelation brothers)
[//]: # (@routeToRelation parent b|返回)

## 同构的动机

[//]: # (@stageName ghost_in_shells_activatioin)

为什么要做同构呢？它源于一个很直观的需求：我能不能用无线耳机，口语操作网页、家电、智能玩具等……成为一个真正的语音 OS ？

[//]: # (@info)

到目前来看，主流的对话 OS 方案是基于硬件和操作系统的。比如苹果、小米、华为等，他们掌握了系统层面的接口。
语音 OS 先和系统对接，系统再通过 API 和软件对接。

[//]: # (@info)

智能音箱也是走类似思路，各种软件或智能家居要预先接入智能音箱的系统。
所以智能音箱变得越来越 "重" 了，内置操作系统、可以对接物联网设备，进一步从音箱添加触屏，变得像平板或者电视了。

[//]: # (@break)

对于对话式 OS 而言，其实物理的硬件载体和操作系统是可以绕过去的。

[//]: # (@info)

每个设备可以通过内建的通信模块，和对应的云端 Shell 进行交互，Shell维护自己的局部状态；
而若干个云端的 Shell，再和统一的状态管理中控 Ghost 对接，分享统一的全局状态，从而形成初步的同构。

[//]: # (@break)

这种常见的分布式系统或许无法解决同步性、一致性、速度要求特别高的场景。但对于对话式交互本身以秒为单位的比特率，应该是够用了。

[//]: # (@break)

单从技术角度设想，未来许多软硬件可能会有自己的云中控（shell），通过技术委员会认可的公共 interface，在用户授权时，接入指定云端状态管理中控（ghost），然后实现同构。

[//]: # (@info)

那时候的人们，只要一个耳机，联通自己和科幻片相似的虚拟助手，就能接入身边的各种智能设备了。电脑、手机、智能音箱，这类硬件、软件、操作系统高度结合的载体未来恐怕会被淘汰掉。


[//]: # (@askChoose)
[//]: # (@routeToRelation brothers)
[//]: # (@routeToRelation parent b|返回)


## 设备同构不是通讯异构

[//]: # (@stageName not_just_communication)

首先需要澄清，这种思路并不是通讯层面上的异构，——比如微信同时有网页版、客户端、手机版。
它其实需要整合差别很大的工程。

[//]: # (@info)

我举一个例子，同为聊天系统:

- 微信公众号是同步响应机制
- 百度智能音箱是同步响应 （也有双工的极客模式）
- 钉钉群可以调API异步响应
- 网页版是 websocket 双工通讯

[//]: # (@info)

将这四种端同构为一个对话机器人，如果在网页版内进行对话，同步客户端如公众号和智能音箱是接受不到的。

[//]: # (@info)

接受不到又不能完全忽略，因为很多指令性的命令、用户身份数据、权限变更等， Shell 仍然要执行。
所以通讯差异只是同构工作早期的一个难题。Ghost in Shells 远比通讯上的同构复杂得多。


[//]: # (@askChoose)
[//]: # (@routeToRelation brothers)
[//]: # (@routeToRelation parent b|返回)


## 更多技术细节

[//]: # (@stageName ghost_in_shells_implements)

我在设计 "Ghost in Shells" 方案时，遭遇和考虑到的各种问题，简单罗列如下：

- ```输入一致化```
- ```输出抽象化```：方便 Shell 建立多模态的输出策略
- ```强制状态同步```：
    - ghost 广播状态变更
    - shell 强制状态变更
- ```Ghost 锁```：防止 Ghost 裂脑
- ```无状态通讯```：不影响中控状态，不用锁的通讯
- ```Ghost 与 Shell 的通讯```
    - 同步广播
    - 同步不广播
    - 异步不广播
    - 异步广播
- ```特异性通讯```：
    - Ghost 内部的异步任务
    - Ghost 内 session 对另一个 session 的(所有 shells 的)通讯
    - shell 对 shell 的直接通讯
    - Ghost 对 shell 的直接通讯
- ```事件广播```：Ghost 全局广播，shell 主动捕捉并执行
- ```指令状态```：Shell 自身的局部状态暴露到 Ghost，可以由其它端触发
    - 指令自动发现
    - 指令集变更
    - 同步状态声明
    - 指令优先匹配与意图响应

[//]: # (@info)

CommuneChatbot v0.2 基于这套技术思路，重构了 v0.1 的所有代码。
现在的架构已经实现了七八成的思路，但到目前只有精力做一个 demo：用微信公众号语音控制网页版。

[//]: # (@askChoose)
[//]: # (@routeToRelation parent b|返回)
[//]: # (@routeToStage ending)


# 结束

[//]: # (@stageName ending)
[//]: # (@goFulfill)

