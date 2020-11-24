<section class="content" style="padding: 10px">
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">搜索</h3>
                </div>


                <div class="card-body">
                    <form role="form" data-form="1" data-search-table-id="1" data-title="自定义标题">
                        <div class="form-inline">
                            <div class="form-group">
                                <label>项目名称：</label>
                                <input type="text" class="form-control" name="project_name" placeholder="">
                            </div>
                            <div class="form-group">
                                <label>项目组：</label>
                                <select class="form-control" name="project_group_id">
                                    {:options,$projectGroups}
                                </select>
                            </div>
                            <div class="form-group" style="margin-left: 10px">
                                <button type="submit" class="btn btn-primary">提交</button>
                                <button type="reset" class="btn btn-default">重置</button>
                            </div>
                            <div class="form-group" style="margin-left: 10px">
                                <button type="button" class="btn btn-primary btn-alert" data-msg="生成成功"
                                        data-on-ok="autoMake">手动生成.json文件
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="content">
    <div class="card-body table-responsive" style="padding-top: 0px">
        <a class="btn   btn-outline-primary btn-table-tool btn-dialog"
           href="{url admin\project add}&__addons={$addons}"
           data-area="30%,50%" title="新增项目">新增</a>
    </div>
    <div class="card-body table-responsive" style="padding-top: 0px">
        <table data-table="1" data-url="{url admin\project ajax_data}&__addons={$addons}" id="table1"
               class="table table-hover">
            <thead>
            <tr>

                <th data-field="id" data-formatter="epiiFormatter">ID</th>
                <th data-field="project_name" data-formatter="epiiFormatter">项目名称</th>
                <th data-field="project_group_name" data-formatter="epiiFormatter">所属项目组</th>
                <!--                <th data-field="project_url" data-formatter="epiiFormatter">项目git地址</th>-->
                <th data-field="create_time" data-formatter="epiiFormatter">添加时间</th>
                <th data-field="update_time" data-formatter="epiiFormatter">更新时间</th>
                <th data-formatter="epiiFormatter.btns"
                    data-btns="jump,edit,ver,ver_import,del"
                    data-edit-url="{url admin\project edit }&id={id}&__addons={$addons}"
                    data-edit-title="编辑：{name}"
                    data-del-url="{url admin\project delete }&id={id}&__addons={$addons}"
                    data-del-title="删除：{name}"
                    data-area="30%,50%"
                >操作
                </th>
            </tr>
            </thead>
        </table>
    </div>

</div>

<script type="text/javascript">
    function ver(field_value, row, index, field_name) {
        var url = "{url admin\\\\version index}&project_id=" + row.id + "&__addons={$addons}";
        return "<a class='btn btn-outline-info btn-sm btn-dialog' href='" + url + "' data-title='版本管理' data-area='90%,90%'>版本管理</a>";
    }

    function ver_import(field_value, row, index, field_name) {
        var url = "{url admin\\\\version add}&project_id=" + row.id + "&__addons={$addons}";
        return "<a class='btn btn-outline-info btn-sm btn-dialog' href='" + url + "' data-title='导入新版本' data-area='30%,50%'>导入新版本</a>";
    }

    function jump(field_value, row, index, field_name) {
        var url = row.project_url;
        return "<a class='btn btn-outline-info btn-sm' href='" + url + "' target='_blank'>跳转</a>";
    }

    function autoMake() {
        $.ajax({
            type: "GET",
            url: "{url admin\\\\project@autoMake}&__addons={$addons}",
            dataType: "json",
            success: function (res) {
                console.log(res)
            },
            error: function (e) {
                console.log(e)
            }
        })
    }
</script>
