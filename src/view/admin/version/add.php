<form role="form" class="epii" method="post" data-form="1" action="">
    <div class="form-group">
        <label>项目来源：</label>
        <select class="selectpicker" id="class" name="source">
            <option value="0">请选择</option>
            {:options,$sources,$source}
        </select>
    </div>
<!--    <div class="form-group">-->
<!--        <label for="class">所属项目组：</label><br>-->
<!--        <select class="selectpicker" id="class" name="project_group_id">-->
<!--            <option value="0">请选择</option>-->
<!--            {:options,$projectGroups,$project.project_group_id?}-->
<!--        </select>-->
<!--    </div>-->
    <div class="form-group">
        <label>项目仓库名：</label>
        <input type="text" class="form-control" name="repo_name" value="{? $repoName}" required
               placeholder="请输入项目名称">
    </div>
    <div class="form-group">
        <label>项目版本号：</label>
        <input type="text" class="form-control" name="version_name" value="{? $versionName}" required
               placeholder="请输入项目版本号">
    </div>
    <div class="form-group">
        <input type="hidden" name="project_id" value="{$project.id?0}">
    </div>
    <div class="form-footer">
        <button type="submit" class="btn btn-primary">提交</button>
    </div>
</form>
