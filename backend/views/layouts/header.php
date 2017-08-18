<?php
    use yii\helpers\Html;
    use common\logic\CompanyUserCenter;
    $session = Yii::$app->getSession();

    //用户拥有的项目列表
    $objCompanyUser = new CompanyUserCenter();
    $arrProjectsList = $objCompanyUser->curlGetUserProjectsById($session['userinfo']['id']);
//echo "<pre>";
//print_r($arrProjectsList);exit;
?>
<style type="text/css">
    *{margin: 0; padding: 0; box-sizing:border-box; -moz-box-sizing:border-box; -webkit-box-sizing:border-box;}
    body{ min-width: 600px; overflow: auto;}
    .header{ position: absolute; top: 0; left: 0; z-index: 1002; background: #32AAFA; width: 100%; height: 48px; min-width: 600px !important;}
    .custom-logo{ overflow: hidden; width: 230px !important; height: 48px !important; line-height: 27px !important;  background: #03A1FF; padding:8px 0 0 20px !important; text-align: left !important;
            -webkit-transition: -webkit-transform .3s ease-in-out,width .3s ease-in-out;
            -moz-transition: -moz-transform .3s ease-in-out,width .3s ease-in-out;
            -o-transition: -o-transform .3s ease-in-out,width .3s ease-in-out;
            transition: transform .3s ease-in-out,width .3s ease-in-out;
    }
    .main-slider{
            position: absolute;
            z-index: 1;
            top: 0;
            left: 0;
            width: 230px;
            height: 100vh;
            padding-top: 48px;
            background-color: #333;
            -webkit-transition: -webkit-transform .3s ease-in-out,width .3s ease-in-out;
            -moz-transition: -moz-transform .3s ease-in-out,width .3s ease-in-out;
            -o-transition: -o-transform .3s ease-in-out,width .3s ease-in-out;
            transition: transform .3s ease-in-out,width .3s ease-in-out;
    }
    .nav-right{position: absolute; top: 0; width: 100%; max-width:1572px;  height: 48px; padding-left: 230px;
        -webkit-transition: -webkit-transform .3s ease-in-out,padding-left .3s ease-in;
        -moz-transition: -moz-transform .3s ease-in-out,paddingLeft .3s ease-in;
        -o-transition: -o-transform .3s ease-in-out,paddingLeft .3s ease-in;
        transition: transform .3s ease-in-out,padding-left .3s ease-in;
    }
    .nav-right .Infrastructure{float: left; cursor: pointer; }
    .nav-right .Infrastructure a{ display: block; width: 60px; height: 48px; background:url(/dist/img/header/Infrastructure.png) 0 0 no-repeat; }
    .nav-right .Infrastructure a:hover{ background-image: url(/dist/img/header/menu_hover.png);}
    .nav-right img{ vertical-align: top;}
    .navigation ul{ margin: 0;}
    .navigation ul li{ float: left; list-style: none; }
    .navigation ul li:first-child a{border-left: 1px solid #2C98E0;}
    .navigation ul li.active a{ background: rgba(0,0,0,0.2); }
    .navigation ul li:hover a{background-color: #2A98E1}
    .navigation ul li a{ overflow: hidden; display: block; font-size: 0; padding: 0 10px; width: 108px; height: 48px; line-height: 48px; text-align: center; color: #fff; text-decoration: none; border-right: 1px solid #2C98E0;}
    .navigation ul li a span{position: relative; font-size: 14px; padding-right: 9px; }
    .navigation ul li i.triangle-down {
            position:absolute;
            right: -3px;
            top: 7px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #fff;
    }
    .navigation ul li ul{ display: none; overflow: hidden; padding: 6px 0; width: 108px; margin-top: 8px;  background: #fff; border-radius: 4px; border-left: 0;box-shadow: 0 2px 10px 0 rgba(0,0,0,0.15); }
    .navigation ul li:hover ul{ display: block;}
    .navigation ul li.active ul li a{ border:0 !important;}
    .navigation ul li ul li a{ border: 0; background: none; height: 36px; line-height: 36px;}
    .navigation ul li.active ul li a{ background: none; font-size: 14px; color: #333;}
    .navigation ul li.active ul li.on a{ color: blue; }
    .navigation ul li.active ul li:hover a{ background: #D6EEFE;}
    .userinfobox{position: relative; float: right; position: relative; top:0; z-index: 2;}
    .userinfobox .userinfo{ display: block; min-width: 101px; height: 48px; padding-top: 12px; font-size: 0; text-decoration: none;}
    .userinfobox .userinfo img{ float: left; margin-right: 8px;}
    .userinfobox .userinfo span{ display: block; font-size: 14px;  color: #fff; line-height: 24px; text-align: center;}
    .userinfobox .userinfo .down{ float: right; margin-top: 3px; margin-right: 7px;}
    .userinfobox li{ list-style: none; width: 116px; padding-left: 5px;}
    .userinfobox li ul{ display: none; width: 108px; padding: 6px 0; border-radius: 4px; margin-top: 8px; box-shadow: 0 2px 10px 0 rgba(0,0,0,0.15);background: #fff;}
    .userinfobox li:hover ul{ display: block;}
    .userinfobox li ul li{ width: 108px; padding-left: 0;}
    .userinfobox li ul li a{ display: block;  width: 108px; padding: 10px 12px; line-height: 15px; text-align:  left; text-decoration: none; font-size: 14px; color: #333;}
    .userinfobox li ul li a:hover{ background: #D6EEFE; }
    .userinfobox li ul li a img{ float: left; margin-right: 12px;}
    .sidebar-mini.sidebar-collapse .main-sidebar{
            -webkit-transform: translate(0, 0);
            -ms-transform: translate(0, 0);
            -o-transform: translate(0, 0);
            transform: translate(0, 0);
            width: 0 !important;
    }
    .content-wrapper{
        padding-top: 48px;
    }
    .sidebar-mini.sidebar-collapse .content-wrapper{
        -webkit-transform: translate(0, 0);
        -ms-transform: translate(0, 0);
        -o-transform: translate(0, 0);
         transition: transform .3s ease-in-out,marginLeft .3s ease-in-out;
        margin-left: 0 !important;
    }
    .sidebar-collapse .custom-logo{
        -webkit-transition: -webkit-transform .3s ease-in-out,paddingLeft .3s ease-in;
        -moz-transition: -moz-transform .3s ease-in-out,paddingLeft .3s ease-in;
        -o-transition: -o-transform .3s ease-in-out,paddingLeft .3s ease-in;
        transition: transform .3s ease-in-out,paddingLeft .3s ease-in;
        width: 0 !important;
        padding: 0 !important;
    }
    .sidebar-collapse .nav-right{
            -webkit-transform: translate(0, 0);
            -ms-transform: translate(0, 0);
            -o-transform: translate(0, 0);
            transition: transform .3s ease-in-out,padding-left .3s ease-in-out;
            padding-left:0;
    }
    .sidebar-collapse .logo{
            -webkit-transform: translate(0, 0);
            -ms-transform: translate(0, 0);
            -o-transform: translate(0, 0);
            transition: transform .3s ease-in-out,width .3s ease-in-out,padding-left .3s ease-in-out,padding-right .3s ease-in-out;
            width:0 !important;
            padding-left:0 !important;
            padding-right:0 !important;
    }
    @media (max-width:1080px ){
        body{ min-width: 1080px !important; overflow: auto;}
        .header{ min-width: 1080px !important;}
        .navigation ul li a{width: 92px;}
         .navigation ul li ul{ width: 92px;}
    }
    @media (max-width:960px ){
        body{ min-width: 960px !important; overflow: auto;}
        .header{ min-width: 960px !important;}
    }
    @media(max-width: 768px){
        .main-sidebar{
            transform: translate(0, 0) !important;
        }
    }
    @media (max-width:640px ){
        body{ min-width: 640px !important; overflow: auto;}
        .header{ min-width: 640px !important;}
        .navigation{ display: none;}

    }
</style>
<script src="/dist/js/jquery-1.7.1.js"></script>
<div class="main-header">
    <div class="header">
        <div class="userinfobox">
            <ul>
                <li>
                    <a class="userinfo" id="userinfo" href="javascript:;"><img class="down" src="/dist/img/header/personalcenter_more.png" alt="">
                        <img src="/dist/img/header/status_avatar.png" alt=""><span><?php echo $session['userinfo']['name'];?></span>
                    </a>
                    <ul id="change_exit">
                        <li class="changepwd"><a href="<?php echo Yii::$app->params['quan_xian']['auth_sso_modify_password_url']; ?>"><img src="/dist/img/header/edit.png" alt="">修改密码</a></li>
                        <li class="exit"><a href="/user/logout"><img src="/dist/img/header/loginout.png" alt="">退出</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="custom-logo">
            <svg t="1499929155484" viewBox="0 0 4726 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1691" xmlns:xlink="http://www.w3.org/1999/xlink" width="120" height="27">
                <path d="M3367.384615 405.661538l-31.507692 63.015385h-118.153846v63.015385h78.769231l-78.769231 157.538461v74.830769h275.692308v78.769231h-279.63077V905.846154h279.63077v102.4h149.661538V905.846154h212.676923v-63.015385h-212.676923v-78.769231h212.676923v-59.076923h-212.676923v-110.276923h-149.661538v110.276923h-133.907693l94.523077-173.292307h401.723077v-63.015385l-370.215384 3.938462 35.446153-66.953847zM4592.246154 405.661538l3.938461 66.953847h-55.138461l-7.876923-66.953847-110.276923 3.938462 7.876923 66.953846-263.876923-3.938461v535.630769l118.153846-94.523077v-252.061539h27.569231v236.307693l102.4-82.707693v-228.430769h-118.153847v-43.323077l145.723077 3.938462 23.63077 252.061538-228.43077 212.676923 240.246154-153.6 11.815385 149.661539h192.984615v-94.523077h-90.584615l-15.753846-126.030769 145.723077-98.461539-63.015385-82.707692-98.461538 90.584615-11.815385-157.538461h110.276923v-133.907693h-66.953846z m-567.138462 0v74.83077H3977.846154v78.76923h47.261538v358.4H3977.846154v90.584616h169.353846V405.661538h-122.092308zM0 562.018462c86.646154-106.338462 204.8-145.723077 334.769231-161.476924 149.661538-19.692308 295.384615-7.876923 445.046154 11.815385 224.492308 27.569231 448.984615 59.076923 677.415384 51.2 271.753846-11.815385 543.507692-39.384615 803.446154-126.030769 43.323077-15.753846 86.646154-31.507692 126.030769-55.138462 59.076923-31.507692 47.261538-51.2-11.815384-94.523077-35.446154-23.630769-98.461538-51.2-141.784616-70.892307-129.969231-55.138462-252.061538-90.584615-417.476923-102.4-240.246154-11.815385-488.369231 39.384615-681.353846 145.723077-74.830769 43.323077-145.723077 90.584615-220.553846 133.907692-23.630769 11.815385-47.261538 19.692308-78.769231 35.446154 66.953846 0 126.030769-11.815385 185.107692-35.446154 70.892308-27.569231 137.846154-55.138462 208.738462-86.646154 236.307692-110.276923 484.430769-133.907692 736.492308-70.892308 94.523077 23.630769 78.769231 7.876923 303.261538 102.4-35.446154 11.815385-55.138462 15.753846-82.707692 23.63077-153.6 39.384615-307.2 66.953846-464.738462 82.707692-157.538462 19.692308-319.015385 31.507692-476.553846 23.630769-145.723077-3.938462-291.446154-15.753846-437.169231-23.630769-118.153846-7.876923-236.307692-15.753846-354.461538-19.692308-106.338462 0-208.738462 15.753846-299.323077 70.892308-70.892308 39.384615-122.092308 94.523077-153.6 165.415385M2000.738462 459.618462c55.138462 0 122.092308-3.938462 177.230769-11.815385 118.153846-15.753846 224.492308-31.507692 342.646154-47.261539 59.076923-7.876923 122.092308-15.753846 185.107692-7.876923 94.523077 11.815385 173.292308 66.953846 240.246154 157.538462 7.876923 11.815385 19.692308 39.384615 27.569231 51.2-3.938462-19.692308-11.815385-55.138462-15.753847-70.892308-39.384615-90.584615-90.584615-153.6-157.538461-200.861538-78.769231-51.2-161.476923-74.830769-252.061539-82.707693-15.753846 0-27.569231 3.938462-39.384615 19.692308-19.692308 27.569231-47.261538 51.2-78.769231 70.892308-90.584615 55.138462-192.984615 78.769231-295.384615 102.4-47.261538 0-90.584615 7.876923-133.907692 19.692308 0-3.938462 0 0 0 0M512 845.587692c-3.938462 51.2-27.569231 90.584615-70.892308 122.092308s-94.523077 47.261538-161.476923 47.261538c-74.830769 0-133.907692-19.692308-177.230769-59.076923s-66.953846-90.584615-66.953846-153.6 23.630769-118.153846 66.953846-153.6 102.4-59.076923 177.230769-59.076923c63.015385 0 118.153846 15.753846 161.476923 43.323077 43.323077 27.569231 66.953846 66.953846 70.892308 114.215385h-106.338462c-11.815385-27.569231-27.569231-43.323077-43.323076-55.138462-19.692308-15.753846-47.261538-23.630769-78.769231-23.630769-39.384615 0-74.830769 19.692308-98.461539 39.384615-23.630769 23.630769-39.384615 55.138462-39.384615 94.523077s15.753846 70.892308 39.384615 94.523077c23.630769 23.630769 55.138462 39.384615 98.461539 39.384616 35.446154 0 63.015385-15.753846 86.646154-31.507693 19.692308-15.753846 39.384615-39.384615 43.323077-59.076923H512zM594.707692 1011.003077v-421.415385h106.338462v157.538462H905.846154v-157.538462h102.4v421.415385H905.846154v-177.230769h-204.8v177.230769zM1106.707692 1011.003077v-421.415385h374.153846v82.707693h-267.815384v70.892307h240.246154v86.646154h-240.246154v90.584616h267.815384v90.584615zM1555.692308 1011.003077h110.276923v-102.4h-110.276923zM2095.261538 888.910769c-3.938462 39.384615-23.630769 66.953846-51.2 90.584616-31.507692 23.630769-70.892308 35.446154-118.153846 35.446153-55.138462 0-98.461538-15.753846-129.96923-43.323076-31.507692-27.569231-47.261538-66.953846-47.261539-114.215385s15.753846-86.646154 47.261539-114.215385c31.507692-27.569231 74.830769-43.323077 129.96923-43.323077 47.261538 0 86.646154 11.815385 118.153846 31.507693 31.507692 19.692308 47.261538 51.2 51.2 82.707692h-82.707692c-3.938462-19.692308-15.753846-31.507692-31.507692-43.323077-15.753846-7.876923-35.446154-15.753846-59.076923-15.753846-31.507692 0-55.138462 7.876923-70.892308 27.569231-15.753846 15.753846-27.569231 39.384615-27.569231 70.892307s7.876923 51.2 27.569231 70.892308c15.753846 15.753846 39.384615 23.630769 70.892308 23.630769 27.569231 0 47.261538-3.938462 63.015384-15.753846 15.753846-11.815385 23.630769-27.569231 27.569231-47.261538h82.707692zM2327.630769 947.987692c31.507692 0 55.138462-7.876923 70.892308-23.630769 15.753846-15.753846 27.569231-39.384615 27.569231-70.892308s-7.876923-51.2-27.569231-70.892307c-15.753846-15.753846-39.384615-23.630769-70.892308-23.63077s-55.138462 7.876923-70.892307 23.63077c-15.753846 15.753846-27.569231 39.384615-27.569231 70.892307 0 27.569231 7.876923 51.2 27.569231 70.892308 15.753846 15.753846 39.384615 23.630769 70.892307 23.630769m-177.230769-94.523077c0-47.261538 15.753846-86.646154 47.261538-114.215384 31.507692-27.569231 74.830769-43.323077 129.969231-43.323077s98.461538 15.753846 129.969231 43.323077c31.507692 27.569231 47.261538 66.953846 47.261538 114.215384s-15.753846 86.646154-47.261538 114.215385c-31.507692 27.569231-74.830769 43.323077-129.969231 43.323077s-98.461538-15.753846-129.969231-43.323077c-31.507692-27.569231-47.261538-66.953846-47.261538-114.215385M2567.876923 1011.003077v-311.138462h102.4l98.461539 220.553847 106.338461-220.553847h98.461539v311.138462h-74.83077v-208.738462l-94.523077 208.738462h-66.953846l-94.523077-212.676923v212.676923z" fill="#fff" p-id="1691"></path>
            </svg>
        </div>
        <div class="nav-right">
            <div class="Infrastructure" id="sidebar-toggle">
                <a></a>
            </div>
            <div class="navigation">
                   <ul>
                       <?php
                            foreach($arrProjectsList as $val){
                                $val['name'] = str_replace('【勿删】', '', $val['name']);
                                if(strpos($val['name'], 'CRM系统') !== false){//crm 项目
                                    if(count($val['roles']) == 0){//只有一个角色
                                        echo "<li><a href=\"{$val['url']}\" ><span>{$val['name']}</span></a></li>";
                                    }
                                    else{
                                        $roleSelectHtml = '';
                                        foreach($val['roles'] as $role)
                                        {
                                            $roleSelectHtml .= "<li><a href=\"{$val['url']}?role_id={$role['id']}\">{$role['name']}</a></li>";
                                        }
                                        echo <<<HTML
                                            <li class="active">
                                                <a href="javascript:;" ><span>{$val['name']}<i class='triangle-down'></i></span></a>
                                                <div class="dropdownbox">
                                                    <ul>
                                                        $roleSelectHtml
                                                    </ul>
                                                </div>
                                            </li>
HTML;
                                    }
                                }
                                else{
                                    echo "<li><a href=\"{$val['url']}\" ><span>{$val['name']}</span></a></li>";
                                }
                            }
                        ?>
                   </ul>
            </div>
        </div>
    </div>
</div>
<script>
            var SidebarToggle = document.getElementById('sidebar-toggle');
            SidebarToggle.onclick = function () {
                if(document.body.getAttribute("class") == 'skin-blue sidebar-mini sidebar-collapse'){
                    document.body.setAttribute("class", "skin-blue sidebar-mini");
                }else{
                    document.body.setAttribute("class", "skin-blue sidebar-mini sidebar-collapse");
                }
            }
</script>

