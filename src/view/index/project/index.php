<html>

<head>
    <title>版本管理</title>
    <style>
        * {
            margin: 0 auto;
            padding: 0;
            border: none;
        }

        #project-group-list {
            border-bottom: 2px black double;
            padding-bottom: 0.2em;
        }

        #project-group-list h2 {
            display: inline-block;
            margin: 0 auto;
            padding: 0 0.2em;
            border: 1px white solid;
        }

        #project-group-list h2.active {
            border-color: black;
        }

        #project-group-detail {
            /*display: none*/
        }

        #project-group-detail .project-group {
            display: none;
        }

        #project-group-detail .project-list {}

        #project-group-detail .project-list .project-title {
            display: inline-block;
            width: 25%;
            min-width: 250px;
            padding: 0.4em 0;
        }

        #project-group-detail .project-list .project-opt {
            display: inline-block;
        }

        #project-group-detail .project-list .project-opt a {
            margin-right: 0.5em;
        }

        #project-group-detail .version-list .version {
            /*版本隐藏*/
            display: none;
        }

        #project-group-detail .version-list .version:first-child {
            /*仅显示第一个版本*/
            display: block;
        }

        #project-group-detail .version-list .version span {
            display: inline-block;
            width: 15%;
            min-width: 170px;
        }

        #project-group-detail .version-list .version a {
            margin-right: 0.5em;
        }
    </style>
    <script src="https://libs.baidu.com/jquery/2.1.4/jquery.min.js"></script>
</head>

<body>
    <div id="guide">
        <a style="color: red;float:right;padding:0.5em;cursor:pointer;" href="javascript:closeGuide()">X</a>
        <p>文始征信仓库全局设置指导</p>
        <p>首先，将公司内部的仓库地址添加到composer配置中。</p>
        <p style="background: #cccccc;">composer config -g repo.packagist wszx http://public.master.composer.wenshi.wszx.cc/</p>
        <p>因为公司仓库地址是http的，而composer中的仓库默认为https的，直接用肯定会报错误，没关系，关掉SSL验证就好了。</p>
        <p style="background: #cccccc;">composer config -g secure-http false</p>
        <!--    <p>最后，更新composer。</p>-->
        <!--    <p style="background: #cccccc;">composer update</p>-->
    </div>
    <div id="project-group-list">
    </div>
    <div id="project-group-detail">
    </div>
</body>

<script type="text/javascript">
    function switchTab(el, groupId) {
        $(el).addClass("active").siblings().removeClass("active");
        $("#project-group-detail .project-group[data-id='" + groupId + "']").show().siblings().hide();
    }

    function showMoreVersion(el) {
        // el=$(el).parent();
        console.log($(el).innerHTML);
        $(el).parent().siblings().show();
    }

    function showLessVersion(el) {

    }

    function closeGuide(){
        $("#guide").hide();
    }

    var Package = {
        projectGroupTemp: '<div class="project-group" data-id="##id##"><h2 class="project-group-title"></h2><div class="project-list">##2##</div></div>',
        projectTemp: '<div class="project"><h4 class="project-title">##1##</h4><div class="project-opt"><a target="_blank" href="##2##">跳转</a><a target="_blank" href="##3##">导入新版本</a></div><div class="version-list">##4##</div></div>',
        // versionTemp: '<div class="version"><span>##1##</span><a class="dl" href="##2##">下载</a><a class="del" href="##3##">删除</a><a href="javascript:void(0)" onclick="showMoreVersion(this)">更多</a></div>',
        versionTemp: '<div class="version"><span>##1##</span><a class="dl" href="##2##">下载</a><a href="javascript:void(0)" onclick="showMoreVersion(this)">更多</a></div>',

        getProjectGroupHtml: function(projectGroupInfo) {
            var html = this.projectGroupTemp;
            html = html.replace("##id##", projectGroupInfo.id);
            var childrenHtml = "";
            for (var i = 0; i < projectGroupInfo.projects.length; i++) {
                var projectInfo = projectGroupInfo.projects[i];
                childrenHtml += this.getProjectHtml(projectInfo);
            }
            html = html.replace("##2##", childrenHtml);

            return html;
        },
        getProjectHtml: function(projectInfo) {
            var html = this.projectTemp;
            html = html.replace("##1##", projectInfo.project_name);
            html = html.replace("##2##", projectInfo.project_url);
            html = html.replace("##3##", projectInfo.add_url);
            html = html.replace("##5##", projectInfo.id);
            var childrenHtml = "";
            for (var i = 0; i < projectInfo.versions.length; i++) {
                var versionInfo = projectInfo.versions[i];
                childrenHtml = this.getVersionHtml(versionInfo, projectInfo.versions.length - i - 1) + childrenHtml;
            }
            html = html.replace("##4##", childrenHtml);

            return html;
        },
        getVersionHtml: function(versionInfo, index) {
            var html = this.versionTemp;
            html = html.replace("##1##", versionInfo.version_name);
            html = html.replace("##2##", versionInfo.version_url);
            html = html.replace("##3##", "javascript:delVersion(" + versionInfo.id + ")");
            if (index > 0) {
                html = html.replace('<a href="javascript:void(0)" onclick="showMoreVersion(this)">更多</a>', '');
            }
            // $(html).children("a.dl").attr("href", versionInfo.version_url);
            // $(html).children("a.del").attr("href", "javascript:delVersion(" + versionInfo.id + ")");
            return html;
        },
        render: function(pkgInfo) {
            for (var i = 0; i < pkgInfo.length; i++) {
                $("#project-group-detail").append(this.getProjectGroupHtml(pkgInfo[i]));
                $("#project-group-list").append('<h2 onclick="switchTab(this, ' + pkgInfo[i].id + ')">' + pkgInfo[i].project_group_name + '</h2>');
            }
        }

    };

    $(function() {
        $.ajax({
            type: "GET",
            url: "{url index\\\\project ajax_data}&__addons={$__addons}",
            dataType: "json",
            async: false,
            success: function(res) {
                // console.log(res);
                Package.render(res);
            },
            error: function(e) {}
        });
    });
</script>

</html>