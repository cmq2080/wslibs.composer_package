<section class="content" style="padding: 10px">
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">搜索</h3>
                </div>


                <div class="card-body">
                    <form role="form" data-form="1" data-search-table-id="1" data-title="自定义标题" >
                        <div class="form-inline"  >
                            <div class="form-group">
                                <label>项目组名称：</label>
                                <input type="text" class="form-control" name="project_group_name" placeholder="">
                            </div>
                            <div class="form-group" style="margin-left: 10px">
                                <button type="submit" class="btn btn-primary">提交</button>
                                <button type="reset" class="btn btn-default">重置</button>
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
        <a class="btn   btn-outline-primary btn-table-tool btn-dialog" href="{url admin\project_group add}&__addons={$addons}" data-area="30%,50%" title="新增管理员">新增</a>
    </div>
    <div class="card-body table-responsive" style="padding-top: 0px">
        <table data-table="1" data-url="{url admin\project_group ajax_data}&__addons={$addons}" id="table1" class="table table-hover">
            <thead>
            <tr>
                <th data-field="id" data-formatter="epiiFormatter">ID</th>
                <th data-field="project_group_name" data-formatter="epiiFormatter">项目组名称</th>
                <th data-field="create_time" data-formatter="epiiFormatter">添加时间</th>
                <th data-field="update_time" data-formatter="epiiFormatter">更新时间</th>
                <th data-formatter="epiiFormatter.btns"
                    data-btns="edit,del"
                    data-edit-url="{url admin\project_group edit }&id={id}&__addons={$addons}"
                    data-edit-title="编辑：{name}"
                    data-del-url="{url admin\project_group delete }&id={id}&__addons={$addons}"
                    data-del-title="删除：{name}"
                    data-area="30%,50%"
                >操作
                </th>
            </tr>
            </thead>
        </table>
    </div>

</div>
