## 应用管理

此处可以对应用进行编辑、删除、变更状态等操作。

----------

##### 创建（编辑）应用

点左侧子菜单的创建应用或者点击应用列表的编辑菜单，进入如下界面。在此，您可以对应用进行各项操作。

| 项目 | 描述 |
| - | - |
| 应用名称 | 应用的名称。 |
| 权限 | 选择该应用具备的各种权限。 |
| 允许通信的 IP | 允许与 baigo SSO 进行通信的 IP 地址，每行一个 IP 地址，可使用通配符 `*`，如：192.168.1.`*`，此时，只有 192.168.1 网段的 IP 地址 允许 通信。 |
| 禁止通信的 IP | 禁止与 baigo SSO 进行通信的 IP 地址，每行一个 IP 地址，可使用通配符 `*`，如：192.168.1.`*`，此时，192.168.1 网段的 IP 地址 禁止 通信。 |
| 状态 | 可选启用、禁用。 |

----------

##### 查看应用

在此，您可以获取调用 API 接口所需要的信息。如果您认为 App Key 或 App Secret 泄露，也可以在此通过重置更换，原 App Key 和 App Secret 将作废。
