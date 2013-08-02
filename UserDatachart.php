<?php
/*
 * check某公司是否有日志记录
 */
function check_enterprise_eventlog($enterpriseno){
    Doo::db()->reconnect('db_oa');
    $query = "SELECT COUNT(DISTINCT enterpriseno) FROM system_eventlog WHERE enterpriseno=$enterpriseno";
    $bool = Doo::db()->fetchRow($query);
    if($bool == null){
        return -1;
    }else{
        return 0;
    }
}
/*
 * 统计公司的用户数
 */
function enterprise_user_total($enterpriseno){
    Doo::db()->reconnect('db_oa');
    $query = "SELECT COUNT(DISTINCT userno) AS total FROM enterprise_user WHERE enterpriseno=$enterpriseno";
    $count = Doo::db()->fetchAll($query);
    return $count[0]['total'];
}
/*
 * 统计oa.cn总人数
 */
function oa_user_total(){
    Doo::db()->reconnect('db_oa');
    Doo::loadClass('Enum');
    $delete = Enum::getStatusType('Delete');
    $query = "SELECT COUNT(DISTINCT userno) AS total FROM users WHERE status!=$delete";
    $count = Doo::db()->fetchAll($query);
    $max = intval($count[0]['total']);
    return $max;
}
/*
 * 图表轴元素定义 
 */
class ChartAxis{
    /*
     * 模块使用曲线图y轴定义
     */
    public function module_yrange($count,$type){
        if($count<100){
            $i = 20;
        }else if($count>=100 && $count<200){
            $i = 40;
        }else if($count>= 200 && $count<500){
            $i = 100;
        }else if($count>=500 && $count<1000){
            $i = 200;
        }else if($count>=1000 && $count<5000){
            $i = 1000;
        }else{
            $i = 4000;
        }
        switch ($type){
            case 'day':
                break;
            case 'week':
                $i *= 5;
                break;
            case 'month':
                $i *= 25;
                break;
        }
        $max = $i * 10;
        return $max;
    }
    public function oa_yrange($count,$type){
        $j = 10;
        $x = intval($count / $j);
        while($x>=10){
            $j *= 10;
            $x = intval($count / $j);
        }
        $each = $j * $x / 2;
        switch($type){
            case 'day':
                break;
            case 'week':
                $each *= 5;
                break;
            case 'month':
                $each *=25;
                break;
        }
        return $each;
    }
    public function login_yrange($count){
        $j = 10;
        $x = intval($count / $j);
        while($x >= 10){
            $j *= 10;
            $x = intval($count / $j);
        }
        $max = $x * $j + $j;
        return $max;
    }
}
class UserDatachart{
    /*
     * oa.cn登录统计图
     */
    public function loginpie(){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $allcount = intval($data->logintotal());
        $webtimes = intval($data->logintotal('webtimes'));
        $iostimes = intval($data->logintotal('iostimes'));
        $androidtimes = intval($data->logintotal('androidtimes'));
        $imtimes = intval($data->logintotal('imtimes'));
        $title = new title("oa.cn所有用户合计登录:{$allcount}人");
        $title->set_style('color: #000000; font-size: 20px');

        $d = array(
            new pie_value($webtimes,"web登录({$webtimes})人"),
            new pie_value($iostimes,"IOS登录({$iostimes})人"),
            new pie_value($androidtimes,"Android登录({$androidtimes})人"),
            new pie_value($imtimes,"IM登录({$imtimes})人"),
            );
        $pie = new pie();
        $pie->alpha(0.5)
            ->add_animation( new pie_fade() )
            ->add_animation( new pie_bounce(5) )
            ->start_angle( 0 )
            ->tooltip( '#percent#' )
            ->colours(array("#d01f3c","#356aa0","#C79810","#f0f0f0"));

        $pie->set_values( $d );
        $chart = new open_flash_chart();
        $chart->set_title( $title );
        $chart->add_element( $pie );
        $chart->set_bg_colour('#FFFFFF');
        echo $chart->toPrettyString();
    }
    /*
     * 公司模块使用
     */
    public function enterprise_module_bar($enterpriseno){
        Doo::loadClass('ofc-library/open-flash-chart');
        $chart = new open_flash_chart();
        Doo::loadClass('UserData');
        $data = new UserData();
        $module = $data->enterprise_module($enterpriseno);
        foreach($module as $k=>$v){
            //$name[] = $v['modulename'];
            //$val[] = intval($v['operatetotal']);
            $tip = $v['modulename'].$v['operatetotal']."次";
            $d[] = new pie_value(intval($v['operatetotal']),$tip);
        }
        $pie = new pie();
        $pie->alpha(0.5)
            ->add_animation( new pie_fade() )
            ->add_animation( new pie_bounce(5) )
            ->start_angle( 0 )
            ->tooltip( '#percent#' );
            //->colours(array("#d01f3c","#356aa0","#C79810","#f0f0f0"));

        $pie->set_values( $d );
        $chart = new open_flash_chart();
        $chart->add_element( $pie );
        $chart->set_bg_colour('#FFFFFF');
        echo $chart->toPrettyString();
    }
    /*
     * oa.cn模块使用
     */
    public function oa_module_pie(){
        Doo::loadClass('ofc-library/open-flash-chart');
        $chart = new open_flash_chart();
        Doo::loadClass('UserData');
        $data = new UserData();
        $module = $data->oa_module();
        foreach($module as $k=>$v){
            //$name[] = $v['modulename'];
            //$val[] = intval($v['operatetotal']);
            $d[] = new pie_value(intval($v['operatetotal']),$v['modulename']);
        }
        $pie = new pie();
        $pie->alpha(0.5)
            ->add_animation( new pie_fade() )
            ->add_animation( new pie_bounce(5) )
            ->start_angle( 0 )
            ->tooltip( '#percent#' );
            //->colours(array("#d01f3c","#356aa0","#C79810","#f0f0f0"));

        $pie->set_values( $d );
        $chart = new open_flash_chart();
        $chart->add_element( $pie );
        $chart->set_bg_colour('#FFFFFF');
        echo $chart->toPrettyString();
    }
    public function login_day_line($date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $s = $data->get_near_7day($date);
        $logintime = $s['time1'];
        $value_all = $data->login_count($logintime);  //总体登录
        $value_web = $data->login_count($logintime,'webtimes'); //web
        $value_and = $data->login_count($logintime,'androidtimes'); //androidtimes
        $value_ios = $data->login_count($logintime,'iostimes'); //ios
        $value_im = $data->login_count($logintime,'imtimes'); //im
        $chart = new open_flash_chart();

        $title = new title('登录按天统计曲线图');

        $line_all = new line();
        $line_web = new line();
        $line_and = new line();
        $line_ios = new line();
        $line_im = new line();
        
        $line_all->set_values($value_all);
        $line_web->set_values($value_web);
        $line_and->set_values($value_and);
        $line_ios->set_values($value_ios);
        $line_im->set_values($value_im);
        
        $line_all->set_colour("#FF6600");
        $line_web->set_colour("#0099FF");
        $line_and->set_colour("#00FF66");
        $line_ios->set_colour("#6600FF");
        $line_im->set_colour("#FF9999");
        
        $line_all->set_key('总体', 11);
        $line_web->set_key('web', 11);
        $line_and->set_key('android', 11);
        $line_ios->set_key('ios', 11);
        $line_im->set_key('im', 11);
       
        $chart->set_title( $title );
        $chart->add_element( $line_all );
        $chart->add_element( $line_web );
        $chart->add_element( $line_and );
        $chart->add_element( $line_ios );
        $chart->add_element( $line_im );
        $x = new x_axis();
        $x->set_steps(3);
        $xlabels = $s['time'];
        $x->set_labels_from_array($xlabels);

        $y = new y_axis();
        $usertotal = oa_user_total();
        $axis = new ChartAxis();
        $max = $axis->login_yrange($usertotal);
        $y->set_range(0,$max);
        $step = intval($max / 10);
        $y->set_steps($step);
        
        $chart->set_y_axis($y);
        $chart->set_x_axis( $x );
        $chart->set_bg_colour('#FFFFFF');
        echo $chart->toPrettyString();
    }
    /*
     * 4周登录曲线图
     */
    public function login_week_line($date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $time = $data->get_near_4week($date);
        $x_labels_arr = $time['week_xlabels'];
        $logintime = $time['week_time'];
        $valueall = $data->login_count_4week($logintime);
        $valueweb = $data->login_count_4week($logintime,'webtimes');
        $valueand = $data->login_count_4week($logintime,'androidtimes');
        $valueios = $data->login_count_4week($logintime,'iostimes');
        $valueim = $data->login_count_4week($logintime,'imtimes');
        $title = new title('登录按周统计曲线图');
        $lineall = new line();
        $lineall->set_colour("#FF6600");
        $lineall->set_key('总体', 11);
        $lineall->set_values($valueall);
        
        $lineweb = new line();
        $lineweb->set_colour("#0099FF");
        $lineweb->set_key('web', 11);
        $lineweb->set_values($valueweb);
        
        $lineand = new line();
        $lineand->set_colour("#FF9999");
        $lineand->set_key('and', 11);
        $lineand->set_values($valueand);
        
        $lineios = new line();
        $lineios->set_colour("#00FF66");
        $lineios->set_key('ios', 11);
        $lineios->set_values($valueios);
        
        $lineim = new line();
        $lineim->set_colour("#6600FF");
        $lineim->set_key('im', 11);
        $lineim->set_values($valueim);
        
        $x = new x_axis();
        $x->set_labels_from_array($x_labels_arr);
        
        $y = new y_axis();
        $usertotal = oa_user_total();
        $axis = new ChartAxis();
        $max = $axis->login_yrange($usertotal);
        $y->set_range(0,$max);
        $step = intval($max / 10);
        $y->set_steps($step);
        
        $chart = new open_flash_chart();
        $chart->set_y_axis($y);
        $chart->set_x_axis($x);
        $chart->set_title($title);
        $chart->add_element($lineall);
        $chart->add_element($lineweb);
        $chart->add_element($lineand);
        $chart->add_element($lineios);
        $chart->add_element($lineim);
        $chart->set_bg_colour('#FFFFFF');
        echo $chart->toPrettyString();
    }
    /*
     * 月登录曲线图
     */
    public function login_month_line($date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $time = $data->get_month($date);
        $valueall = $data->login_count_month($time);
        $valueweb = $data->login_count_month($time,'webtimes');
        $valueand = $data->login_count_month($time,'androidtimes');
        $valueios = $data->login_count_month($time,'iostimes');
        $valueim = $data->login_count_month($time,'imtimes');
        $title = new title('登录按月统计曲线图');
        $lineall = new line();
        $lineall->set_colour("#FF6600");
        $lineall->set_key('总体', 11);
        $lineall->set_values($valueall);
        
        $lineweb = new line();
        $lineweb->set_colour("#0099FF");
        $lineweb->set_key('web', 11);
        $lineweb->set_values($valueweb);
        
        $lineand = new line();
        $lineand->set_colour("#FF9999");
        $lineand->set_key('and', 11);
        $lineand->set_values($valueand);
        
        $lineios = new line();
        $lineios->set_colour("#00FF66");
        $lineios->set_key('ios', 11);
        $lineios->set_values($valueios);
        
        $lineim = new line();
        $lineim->set_colour("#6600FF");
        $lineim->set_key('im', 11);
        $lineim->set_values($valueim);
        
        $x = new x_axis();
        $x->set_steps(2);
        $x->set_labels_from_array($time);
        
        $y = new y_axis();
        $usertotal = oa_user_total();
        $axis = new ChartAxis();
        $max = $axis->login_yrange($usertotal);
        $y->set_range(0,$max);
        $step = intval($max / 10);
        $y->set_steps($step);
        
        $chart = new open_flash_chart();
        $chart->set_y_axis($y);
        $chart->set_x_axis($x);
        $chart->set_title($title);
        $chart->add_element($lineall);
        $chart->add_element($lineweb);
        $chart->add_element($lineand);
        $chart->add_element($lineios);
        $chart->add_element($lineim);
        $chart->set_bg_colour('#FFFFFF');
        echo $chart->toPrettyString();
    }
    /*
     * 用户数按天统计line-bar图
     */
    public function user_line_bar($date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $s = $data->get_near_7day($date);
        $search_time = $s['time1'];
        $userline_value = $data->user_add_7day($search_time);
        $enterprisebar_value = $data->enterprise_add_7day($search_time);
        
        $x = new x_axis();
        $x->set_steps(3);
        $x->set_labels_from_array($s['time']);
        
        $line = new line();
        $line->set_values($userline_value);
        $line->set_colour("#00FF00");
        $line->set_key("个人用户", 11);
        
        $bar = new bar();
        $bar->set_values($enterprisebar_value);
        $bar->set_colour("#FF3333");
        $bar->set_key("企业用户", 11);
        
        $y = new y_axis();
        $y->set_range(0, 2000*10);
        $y->set_steps(2000);
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($line);
        $chart->add_element($bar);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    /*
     * oa.cn用户数按周统计line_bar图
     */
    public function user_line_bar_week($date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $s = $data->get_near_4week($date);
        $search_time = $s['week_time'];
        $userline_value = $data->user_add_4week($search_time);
        $enterprisebar_value = $data->enterprise_add_4week($search_time);
        
        $x = new x_axis();
        $x->set_labels_from_array($s['week_xlabels']);
        
        $line = new line();
        $line->set_values($userline_value);
        $line->set_colour("#00FF00");
        $line->set_key("个人用户", 11);
        
        $bar = new bar();
        $bar->set_values($enterprisebar_value);
        $bar->set_colour("#FF3333");
        $bar->set_key("企业用户", 11);
        
        $y = new y_axis();
        $y->set_range(0, 2000*10);
        $y->set_steps(2000);
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($line);
        $chart->add_element($bar);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    /*
     * oa.cn用户数按月统计line-bar图
     */
    public function user_line_bar_month($date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $time = $data->get_month($date);
        $userline_value = $data->user_add_month($time);
        $enterprisebar_value = $data->enterprise_add_month($time);
        
        $x = new x_axis();
        $x->set_steps(2);
        $x->set_labels_from_array($time);
        
        $line = new line();
        $line->set_values($userline_value);
        $line->set_colour("#00FF00");
        $line->set_key("个人用户", 11);
        
        $bar = new bar();
        $bar->set_values($enterprisebar_value);
        $bar->set_colour("#FF3333");
        $bar->set_key("企业用户", 11);
        
        $y = new y_axis();
        $y->set_range(0, 2000*10);
        $y->set_steps(2000);
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($line);
        $chart->add_element($bar);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    /*
     * oa.cn各模块使用曲线图
     */
    public function module_line($mid,$date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $s = $data->get_near_7day($date);
        $search_time = $s['time1'];
        if($mid == 'all'){
            $mid = '';
            $times_value = $data->times_near_7day($mid, $search_time,0);
            $user_value = $data->login_count($search_time);
        }else{
            $times_value = $data->times_near_7day($mid, $search_time,0);
            $user_value = $data->times_near_7day($mid, $search_time, 1);
        }
        $timesline = new line();
        $timesline->set_colour("#00FF33");
        $timesline->set_key("使用次数", 11);
        $timesline->set_values($times_value);
        
        $userline = new line();
        $userline->set_colour("#9900CC");
        $userline->set_key("使用人数", 11);
        $userline->set_values($user_value);
        
        $x = new x_axis();
        $x->set_steps(3);
        $x->set_labels_from_array($s['time']);

        $usertotal = oa_user_total();
        $axis = new ChartAxis();
        $each = $axis->oa_yrange($usertotal,'day');
        $y = new y_axis();
        $y->set_range(0, $each*10);
        $y->set_steps($each);
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($timesline);
        $chart->add_element($userline);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    public function module_line_week($mid,$date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $s = $data->get_near_4week($date);
        $search_time = $s['week_time'];
        if($mid == 'all'){
            $mid = '';
            $times_value = $data->times_4week($mid, $search_time,0);
            $user_value = $data->login_count_4week($search_time);
        }else{
            $times_value = $data->times_4week($mid, $search_time,0);
            $user_value = $data->times_4week($mid, $search_time, 1);
        }
        //var_dump($times_value,$user_value);die;
        $timesline = new line();
        $timesline->set_colour("#00FF33");
        $timesline->set_key("使用次数", 11);
        $timesline->set_values($times_value);
        
        $userline = new line();
        $userline->set_colour("#9900CC");
        $userline->set_key("使用人数", 11);
        $userline->set_values($user_value);
        
        $x = new x_axis();
        $x->set_labels_from_array($s['week_xlabels']);
        
        $usertotal = oa_user_total();
        $axis = new ChartAxis();
        $each = $axis->oa_yrange($usertotal,'week');
        $y = new y_axis();
        $y->set_range(0, $each*10);
        $y->set_steps($each);
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($timesline);
        $chart->add_element($userline);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    public function module_line_month($mid,$date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $time = $data->get_month($date);
        if($mid == 'all'){
            $mid = '';
            $times_value = $data->times_month($mid, $time,0);
            $user_value = $data->login_count_month($time);
        }else{
            $times_value = $data->times_month($mid, $time,0);
            $user_value = $data->times_month($mid, $time, 1);
        }
        //var_dump($times_value,$user_value);die;
        $timesline = new line();
        $timesline->set_colour("#00FF33");
        $timesline->set_key("使用次数", 11);
        $timesline->set_values($times_value);
        
        $userline = new line();
        $userline->set_colour("#9900CC");
        $userline->set_key("使用人数", 11);
        $userline->set_values($user_value);
        
        $x = new x_axis();
        $x->set_steps(2);
        $x->set_labels_from_array($time);
        
        $usertotal = oa_user_total();
        $axis = new ChartAxis();
        $each = $axis->oa_yrange($usertotal,'month');
        $y = new y_axis();
        $y->set_range(0, $each*10);
        $y->set_steps($each);
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($timesline);
        $chart->add_element($userline);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    public function enterprise_module_day($enterpriseno,$mid,$date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $s = $data->get_near_7day($date);
        $search_time = $s['time1'];
        if($mid == 'all'){
            $mid = '';
            $times_value = $data->enterprise_module_day($enterpriseno,$mid, $search_time,0);
            $login = new LoginlogAction();
            $login->enterprise_login_7day($enterpriseno,$search_time);
            $user_value = $login->enterprise_login;
        }else{
            $times_value = $data->enterprise_module_day($enterpriseno,$mid,$search_time,'0');
            $user_value = $data->enterprise_module_day($enterpriseno,$mid,$search_time,'1');
        }
        $timesline = new line();
        $timesline->set_colour("#00FF33");
        $timesline->set_key("使用次数", 11);
        $timesline->set_values($times_value);
        
        $userline = new line();
        $userline->set_colour("#9900CC");
        $userline->set_key("使用人数", 11);
        $userline->set_values($user_value);
        
        $x = new x_axis();
        $x->set_steps(3);
        $x->set_labels_from_array($s['time']);

        $enterprise_user_total = enterprise_user_total($enterpriseno);
        $axis = new ChartAxis();
        $max = $axis->module_yrange($enterprise_user_total,'day');
        $y = new y_axis();
        $y->set_range(0, $max);
        $y->set_steps($max/10);
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($timesline);
        $chart->add_element($userline);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    public function enterprise_module_week($enterpriseno,$mid,$date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $s = $data->get_near_4week($date);
        $search_time = $s['week_time'];
        if($mid == 'all'){
            $mid = '';
            $times_value = $data->enterprise_module_week($enterpriseno,$mid, $search_time,0);
            $login = new LoginlogAction();
            $login->enterprise_login_4week($enterpriseno, $search_time);
            $user_value = $login->total_4week;
        }else{
            $times_value = $data->enterprise_module_week($enterpriseno,$mid, $search_time,0);
            $user_value = $data->enterprise_module_week($enterpriseno,$mid, $search_time, 1);
        }
        //var_dump($times_value,$user_value);die;
        $timesline = new line();
        $timesline->set_colour("#00FF33");
        $timesline->set_key("使用次数", 11);
        $timesline->set_values($times_value);
        
        $userline = new line();
        $userline->set_colour("#9900CC");
        $userline->set_key("使用人数", 11);
        $userline->set_values($user_value);
        
        $x = new x_axis();
        $x->set_labels_from_array($s['week_xlabels']);
        
        $enterprise_user_total = enterprise_user_total($enterpriseno);
        $axis = new ChartAxis();
        $max = $axis->module_yrange($enterprise_user_total,'week');
        $y = new y_axis();
        $y->set_range(0, $max);
        $y->set_steps($max/10);
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($timesline);
        $chart->add_element($userline);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    public function enterprise_module_month($enterpriseno,$mid,$date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $time = $data->get_month($date);
        if($mid == 'all'){
            $mid = '';
            $times_value = $data->enterprise_module_month($enterpriseno,$mid, $time,0);
            $login = new LoginlogAction();
            $login->enterprise_login_month($enterpriseno, $time);
            $user_value = $login->total_month;
        }else{
            $times_value = $data->enterprise_module_month($enterpriseno,$mid, $time,0);
            $user_value = $data->enterprise_module_month($enterpriseno,$mid, $time, 1);
        }
        //var_dump($times_value,$user_value);die;
        $timesline = new line();
        $timesline->set_colour("#00FF33");
        $timesline->set_key("使用次数", 11);
        $timesline->set_values($times_value);
        
        $userline = new line();
        $userline->set_colour("#9900CC");
        $userline->set_key("使用人数", 11);
        $userline->set_values($user_value);
        
        $x = new x_axis();
        $x->set_steps(2);
        $x->set_labels_from_array($time);
        
        $enterprise_user_total = enterprise_user_total($enterpriseno);
        $axis = new ChartAxis();
        $max = $axis->module_yrange($enterprise_user_total,'month');
        $y = new y_axis();
        $y->set_range(0, $max);
        $y->set_steps($max/10);
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($timesline);
        $chart->add_element($userline);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    /*
     * 按天统计使用率柱状图
     */
    public function chart_present_day($enterpriseno,$mid,$date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $action = new CompositeFunction();
        $s = $data->get_near_7day($date);
        $search_time = $s['time1'];
        $present = $action->module_present_day($enterpriseno, $search_time, $mid);
        $x = new x_axis();
        $x->set_steps(3);
        $x->set_labels_from_array($s['time']);
        $bar = new bar();

        $bar->set_values($present);
        $bar->set_colour("#FF3333");
        $bar->set_key("使用率", 11);
        
        $y = new y_axis();
        $y->set_range(0, 100);
        $y->set_steps(10);
        
        $y_legend = new y_legend( '百分比' );
        $y_legend->set_style( '{font-size: 20px; color: #778877}' );

        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($bar);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        $chart->set_y_legend( $y_legend );
        echo $chart->toPrettyString();
    }
    public function chart_present_week($enterpriseno,$mid,$date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $action = new CompositeFunction();
        $s = $data->get_near_4week($date);
        $search_time = $s['week_time'];
        $present = $action->module_present_week($enterpriseno, $search_time, $mid);
        $x = new x_axis();
        $x->set_labels_from_array($s['week_xlabels']);
        $bar = new bar();

        $bar->set_values($present);
        $bar->set_colour("#FF3333");
        $bar->set_key("使用率", 11);
        
        $y = new y_axis();
        $y->set_range(0, 100);
        $y->set_steps(10);

        $y_legend = new y_legend( '百分比' );
        $y_legend->set_style( '{font-size: 20px; color: #778877}' );
        
        $chart = new open_flash_chart();
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($bar);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        //y轴说明
        $chart->set_y_legend( $y_legend );
        echo $chart->toPrettyString();
    }
    /*
     * 按月统计使用率柱状图
     */
    public function chart_present_month($enterpriseno,$mid,$date){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $action = new CompositeFunction();
        $time = $data->get_month($date);
        $present = $action->module_present_month($enterpriseno, $time, $mid);
        $x = new x_axis();
        $x->set_steps(2);
        $x->set_labels_from_array($time);
        $bar = new bar();

        $bar->set_values($present);
        $bar->set_colour("#FF3333");
        $bar->set_key("使用率", 11);
        
        $y = new y_axis();
        $y->set_range(0, 100);
        $y->set_steps(10);

        $y_legend = new y_legend( '百分比' );
        $y_legend->set_style( '{font-size: 20px; color: #778877}' );

        $chart = new open_flash_chart();
        $chart->set_y_legend( $y_legend );
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($bar);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
    /*
     * 柱状图
     */
    public function chart_module_present($mid,$date,$type){
        Doo::loadClass('ofc-library/open-flash-chart');
        Doo::loadClass('UserData');
        $data = new UserData();
        $x = new x_axis();
        switch ($type){
            case 'day':
                $time = $data->get_near_7day($date);
                $x_labels = $time['time'];
                $time_arr = $time['time1'];
                $x->set_steps(3);
                break;
            case 'week':
                $time = $data->get_near_4week($date);
                $x_labels = $time['week_xlabels'];
                $time_arr = $time['week_time'];
                break;
            case 'month':
                $time = $data->get_month($date);
                $x_labels = $time_arr =$time;
                $x->set_steps(2);
                break;
        }

        $x->set_labels_from_array($x_labels);
        $bar = new bar();
        $event = new EventAction();
        $present = array();
        $event->oa_module_present($present, $mid, $time_arr, $type);
        $bar->set_values($present);
        $bar->set_colour("#FF3333");
        $bar->set_key("使用率", 11);
        
        $y = new y_axis();
        $y->set_range(0, 100);
        $y->set_steps(10);

        $y_legend = new y_legend( '百分比' );
        $y_legend->set_style( '{font-size: 20px; color: #778877}' );
        
        $chart = new open_flash_chart();
        $chart->set_y_legend( $y_legend );
        $chart->set_bg_colour("#FFFFFF");
        $chart->add_element($bar);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        echo $chart->toPrettyString();
    }
}


class EventlogFields{
    
    /*
     * 
     */
    public function addFields(){
        
    }
}
?>