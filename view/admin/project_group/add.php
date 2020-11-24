<form role="form" class="epii" method="post" data-form="1" action="{if isset($projectGroup)===false}{url admin\project_group add}{else}{url admin\project_group edit}{/if}&__addons={$addons}">

    <div class="form-group">
        <label>项目组名称：</label>
        <input type="text" class="form-control" name="project_group_name" value="{? $projectGroup.project_group_name}" required
               placeholder="请输入项目名称">
    </div>
    <div class="form-group">
        <input type="hidden" name="id" value="{$projectGroup.id?0}">
    </div>
    <div class="form-footer">
        <button type="submit" class="btn btn-primary">提交</button>
    </div>
</form>
