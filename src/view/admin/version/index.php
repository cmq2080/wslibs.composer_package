<!--<section class="content" style="padding: 10px">-->
<!--    <div class="row">-->
<!--        <div class="col-12">-->
<!--            <div class="card card-default">-->
<!--                <div class="card-header">-->
<!--                    <h3 class="card-title">搜索</h3>-->
<!--                </div>-->
<!---->
<!---->
<!--                <div class="card-body">-->
<!--                    <form role="form" data-form="1" data-search-table-id="1" data-title="自定义标题" >-->
<!--                        <div class="form-inline"  >-->
<!--                            <div class="form-group">-->
<!--                                <label  >项目名称：</label>-->
<!--                                <input type="text" class="form-control" name="project_name" placeholder="">-->
<!--                            </div>-->
<!--                            <div class="form-group">-->
<!--                                <label  >项目组：</label>-->
<!--                                <select class="form-control" name="project_group_id">-->
<!--                                    {:options,$projectGroups}-->
<!--                                </select>-->
<!--                            </div>-->
<!--                            <div class="form-group" style="margin-left: 10px">-->
<!--                                <button type="submit" class="btn btn-primary">提交</button>-->
<!--                                <button type="reset" class="btn btn-default">重置</button>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </form>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->




<div class="content">
    <div class="card-body table-responsive" style="padding-top: 0px">
        <a class="btn btn-outline-primary btn-table-tool btn-dialog" href="{url admin\\version add}&project_id={$project.id}&__addons={$__addons}" data-area="30%,50%" title="新增版本">新增版本</a>
    </div>
    <div class="card-body table-responsive" style="padding-top: 0px">
        <table data-table="1" data-url="{url admin\\version ajax_data}&project_id={$project.id}&__addons={$__addons}" id="table1" class="table table-hover">
            <thead>
            <tr>

                <th data-field="id" data-formatter="epiiFormatter">ID</th>
                <th data-field="source" data-formatter="epiiFormatter">版本来源</th>
                <th data-field="version_name" data-formatter="epiiFormatter">版本名称</th>
<!--                <th data-field="version_url" data-formatter="epiiFormatter">版本包地址</th>-->
                <th data-field="create_time" data-formatter="epiiFormatter">添加时间</th>
                <th data-field="update_time" data-formatter="epiiFormatter">更新时间</th>
                <th data-formatter="epiiFormatter.btns"
                    data-btns="download,del"
                    data-edit-url="{url admin\\version edit }&version_id={id}&__addons={$__addons}"
                    data-edit-title="编辑：{version_name}"
                    data-del-url="{url admin\\version delete }&version_id={id}&__addons={$__addons}"
                    data-del-title="删除：{version_name}"
                    data-area="30%,50%"
                >操作
                </th>
            </tr>
            </thead>
        </table>
    </div>

</div>

<script type="text/javascript">
    function ver(field_value, row, index,field_name) {
        var url="{url admin\\\\version index}&id="+row.id+"&__addons={$addons}";
        return "<a class='btn btn-outline-info btn-sm btn-dialog' href='"+url+"'>详情</a>";
    }
    function download(field_value, row, index,field_name) {
        var url=row.version_url;
        return "<a class='btn btn-outline-info btn-sm' href='"+url+"'>下载</a>";
    }
</script>
