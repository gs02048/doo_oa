<?php
/*
 * user_loginlog_fields
 */
class LoginlogAction{
    public $enterprise_use_total; //公司累计登录人数
    public $enterprise_use_present; //公司oa.cn累积使用率
    public $enterprise_login = array();
    public $total_4week = array();
    public $total_month = array();
    private $logintype = array('total'=>'全部登录','webtimes'=>'web登录','androidtimes'=>'android登录','iostimes'=>'ios登录','imtimes'=>'im登录');
    
    /*
     * 登录累计统计
     */
    public function logintotal(){
        Doo::db()->reconnect('db_oa');
        $select = '';
        $C_arr = array();
        foreach($this->logintype as $k=>$v){
            $select_sp = strlen($select) ? ',' : '';
            $sub_select1 = "(SELECT COUNT(DISTINCT userno) FROM user_loginlog";
            $where_str = ' LIMIT 1) AS total';
            if($k != 'total'){
                $where_str = ' WHERE '.$k.'>0 LIMIT 1) AS '.$k;
            }
            $select .= $select_sp.$sub_select1.$where_str;
            $sub_arr = array();
            $sub_arr['name'] = $v;
            $sub_arr['type'] = $k;
            $C_arr[] = $sub_arr;
        }
        $query = 'SELECT '.$select.' FROM user_loginlog LIMIT 1';
        $list = Doo::db()->fetchAll($query);
        foreach ($C_arr as $key=>$value){
            $C_arr[$key]['total'] = $list[0][$value['type']];
            $x = ($list[0][$value['type']] *100) / $list[0]['total'];
            $present = number_format("$x",2);
            $C_arr[$key]['present'] = $present;
        }
        return $C_arr;
    }
    /*
     * 统计公司累计登录人数
     */
    public function enterprise_login_total($enterpriseno){
        Doo::db()->reconnect('db_oa');
        Doo::loadClass('UserDatachart');
        $query = "SELECT COUNT(DISTINCT userno) AS total FROM user_loginlog_fields WHERE enterpriseno=$enterpriseno LIMIT 1";
        $list = Doo::db()->fetchAll($query);
        $this->enterprise_use_total = $list[0]['total'];
        $enterprise_usertotal = enterprise_user_total($enterpriseno);
        $present = ($this->enterprise_use_total*100) / $enterprise_usertotal;
        $this->enterprise_use_present = number_format("$present",2);
    }
    /*
     * 公司最近30天登录统计
     */
    public function enterprise_login_7day($enterpriseno,$time_arr){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? ',' : '';
            $query .= $query_sp."(SELECT COUNT(DISTINCT userno) FROM user_loginlog_fields WHERE logindate='".$v."' AND enterpriseno=$enterpriseno LIMIT 1) as '".$v."'";
        }
        $count_query = 'SELECT '.$query.' from user_loginlog_fields LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $this->enterprise_login[] = intval($v); 
        }
    }
    /*
     * 4周
     */
    public function enterprise_login_4week($enterpriseno,$time_arr){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? ',' : '';
            $query .= $query_sp."(SELECT COUNT(DISTINCT userno) FROM user_loginlog_fields WHERE enterpriseno=$enterpriseno AND logindate>='".$v[0]."' AND logindate<='".$v[1]."' LIMIT 1) as '".$v[0]."'";
        }
        $count_query = 'SELECT '.$query.' from user_loginlog_fields LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $this->total_4week[] = intval($v); 
        }
    }
    public function enterprise_login_month($enterpriseno,$time_arr){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? ',' : '';
            $query .= $query_sp."(SELECT COUNT(DISTINCT userno) FROM user_loginlog_fields WHERE enterpriseno=$enterpriseno AND DATE_FORMAT(logindate,'%X-%m')='".$v."' LIMIT 1) as '".$v."'";
        }
        $count_query = 'SELECT '.$query.' from user_loginlog_fields LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $this->total_month[] = intval($v); 
        }
    }
}
/*
 * 用户模块
 */
class UserData{
    /*
     * 个人用户列表页数据
     */
    public function get_userdata($param){
        Doo::db()->reconnect('db_oa');
        $sql_where = $this->do_query($param);
        $query_user = $this->do_desc($param,$sql_where);
        $count = $this->count($sql_where);
        $userdata_tmp = Doo::db()->fetchAll($query_user);
        $userdata = $this->format_data($userdata_tmp);
        return array('userdata'=>$userdata,'count'=>$count);
    }
    /*
     * 获取完整的列表数据
     */
    public function format_data($list){
        if(!empty($list)){
            Doo::db()->reconnect('db_oa');
            $userno_str = '';
            foreach ($list as $k=>$v){
                $enter_sp = strlen($userno_str) ? ',' : '';
                $userno_str .= $enter_sp.$v['userno'];
            }
            $query_statistic = "SELECT userno,activevalue,webtimes,(androidtimes+iostimes) AS mobiletimes,lastlogintime FROM user_statistics WHERE userno IN ({$userno_str})";
            $statistic = Doo::db()->fetchAll($query_statistic);
            foreach ($statistic as $k=>$v){
                $active[$v['userno']] = $v['activevalue'];
                $webtime[$v['userno']] = $v['webtimes'];
                $mobiletime[$v['userno']] = $v['mobiletimes'];
                $logintime[$v['userno']] = $v['lastlogintime'];
            }
            foreach($list as $k=>$v){
                if(!empty($v['username'])){
                    $name_arr = $this->sub_str($v['username'], 6);
                    $list[$k]['username1'] = $name_arr['substr'];
                    $list[$k]['islong'] = $name_arr['islong'];
                }else{
                    $list[$k]['username1'] = '';
                    $list[$k]['islong'] = 0;
                }
                $list[$k]['mobile'] = empty($v['mobile']) ? '&nbsp' : $v['mobile'];
                $list[$k]['email'] = empty($v['email']) ? '&nbsp' : $v['email'];
                $list[$k]['active'] = empty($active[$v['userno']]) ? 0 : $active[$v['userno']] * 100;
                $list[$k]['webtimes'] = empty($webtime[$v['userno']]) ? 0 : $webtime[$v['userno']];
                $list[$k]['mobiletimes'] = empty($mobiletime[$v['userno']]) ? 0 : $mobiletime[$v['userno']];
                $list[$k]['lastlogintime'] = empty($logintime[$v['userno']]) ? '' : $logintime[$v['userno']];
            }
        }
        return $list;
    }
    /*
     * 统计符合条件的个人用户条数
     */
    public function count($where){
        Doo::db()->reconnect('db_oa');
        $sql_count = "SELECT COUNT(a.userno) AS total FROM users a ";
        $where_sp = strlen($where) ? ' WHERE ' : '';
        $sql_count .= $where_sp.$where;
        $count_tmp = Doo::db()->fetchAll($sql_count);
        $count = $count_tmp[0]['total'];
        return $count;
    }
    /*
     * 选择某种排序时
     */
    public function do_desc($param,$where){
        $query_user = "SELECT a.userno,a.username,a.email,a.mobile FROM users a ";
        $sql_desc = " ORDER BY a.registertime DESC";
        $sql_limit = ' LIMIT '.$param['limit'];
        $where_sp = strlen($where) ? ' WHERE ' : '';
        if(!empty($param['desc'])){
            $desc = $param['desc'];
            switch ($desc){
                case 'registertime':
                    $query_user .= $where_sp.$where.$sql_desc.$sql_limit;
                    break;
                default :
                    $where_sp = strlen($where) ? ' AND ' : '';
                    $query = 'SELECT a.userno,a.username,a.email,a.mobile,b.'.$desc.' FROM users a INNER JOIN user_statistics b ON a.userno=b.userno';
                    $sql_desc = ' ORDER BY b.'.$desc.' DESC ';
                    $query_user = $query.$where_sp.$where.$sql_desc.$sql_limit;
                    break;
            }
        }else{
            $query_user .= $where_sp.$where.$sql_desc.$sql_limit;
        }
       // echo $query_user;die;
        return $query_user;
    }
    /*
     * 个人用户查询sql where条件
     */
    public function do_query($param){
        $where_str = '';
        if(!empty($param['username'])){
            $sql_where_sp = strlen($where_str) ? ' AND ' : '';
            $sql_username = " a.username LIKE '%" . $param['username'] . "%'";
            $where_str .= $sql_where_sp.$sql_username;
        }
        if(!empty($param['userno'])){
            $sql_where_sp = strlen($where_str) ? ' AND ' : '';
            $sql_userno = " a.userno='" . $param['userno'] . "'";
            $where_str .= $sql_where_sp.$sql_userno;
        }
        if(!empty($param['telno'])){
            $sql_where_sp = strlen($where_str) ? ' AND ' : '';
            $sql_mobile = " a.mobile LIKE '%" . $param['telno'] . "%'";
            $where_str .= $sql_where_sp.$sql_mobile;
        }
        if(!empty($param['emailno'])){
            $sql_where_sp = strlen($where_str) ? ' AND ' : '';
            $sql_email = " a.email LIKE '%" . $param['emailno'] . "%'";
            $where_str .= $sql_where_sp.$sql_email;
        }
        if(!empty($param['enterprisename']) && empty($param['enterpriseno'])){
            $enterpriseno_str = $this->getUserByEnterprisename($param['enterprisename']);
            if(!empty($enterpriseno_str)){
                $userno_str1 = $this->getUserByEnterpriseno($enterpriseno_str);
            }
            if(!empty($userno_str1)){
                $sql_where_sp = strlen($where_str) ? ' AND ' : '';
                $sql_enterprisename = " a.userno IN (".$userno_str1.")";
                $where_str .= $sql_where_sp.$sql_enterprisename; 
            }else{
                $where_str = " a.userno=0";
            }
        }
        if(!empty($param['enterpriseno'])){
            $userno_str2 = $this->getUserByEnterpriseno($param['enterpriseno']);
            if(!empty($userno_str2)){
                $sql_where_sp = strlen($where_str) ? ' AND ' : '';
                $sql_enterpriseno = " a.userno IN (".$userno_str2.")";
                $where_str .= $sql_where_sp.$sql_enterpriseno; 
            }else{
                $where_str = " a.userno=0";
            }
        }
        if(!empty($param['max_active'])){
            $userno_str3 = $this->getUserByActive($param['min_active'], $param['max_active']);
            if(!empty($userno_str3)){
                $sql_where_sp = strlen($where_str) ? ' AND ' : '';
                $sql_active = " a.userno IN (".$userno_str3.")";
                $where_str .= $sql_where_sp.$sql_active; 
            }else{
                $where_str = " a.userno=0";
            }
        }
        if(!empty($param['lastlogin'])){
            $userno_str4 = $this->getUserByLogin($param['lastlogin']);
            if(!empty($userno_str4)){
                $sql_where_sp = strlen($where_str) ? ' AND ' : '';
                $sql_lastlogin = " a.userno IN (".$userno_str4.")";
                $where_str .= $sql_where_sp.$sql_lastlogin; 
            }else{
                $where_str = " a.userno=0";
            }
        }
        return $where_str;
    }
    /*
     * 通过最后登录日期查询用户
     */
    public function getUserByLogin($lastlogin){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT userno FROM user_statistics WHERE logindate='{$lastlogin}'";
        $part_list = Doo::db()->fetchAll($query);
        $userno_str = '';
        if(!empty($part_list)){
            $userno_arr = '';
            foreach ($part_list as $k=>$v){
                $userno_arr[] = $v['userno'];
            }
            $userno_str = implode(',', $userno_arr);
        }
        return $userno_str;
    }
    /*
     * 通过活跃度来查询用户
     */
    public function getUserByActive($min_active,$max_active){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT userno FROM user_statistics WHERE activevalue BETWEEN {$min_active} AND {$max_active}";
        $tmp_list = Doo::db()->fetchAll($query);
        $userno_str = '';
        if(!empty($tmp_list)){
            $userno_arr = '';
            foreach($tmp_list as $k=>$v){
                $userno_arr[] = $v['userno'];
            }
            $userno_str = implode(',', $userno_arr);
        }
        return $userno_str;
    }
    /*
     * 通过公司号查询用户
     */
    public function getUserByEnterpriseno($enterpriseno){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT userno FROM enterprise_user WHERE enterpriseno IN (".$enterpriseno.")";
        $usernolist = Doo::db()->fetchAll($query);
        $userno_str = '';
        if(!empty($usernolist)){
            $userno_arr = '';
            foreach($usernolist as $k=>$v){
                $userno_arr[] = $v['userno'];
            }
            $userno_str = implode(',', $userno_arr);
        }
        return $userno_str;
    }
    /*
     * 通过公司名查询用户
     */
    public function getUserByEnterprisename($enterprisename){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno FROM enterprises WHERE name LIKE '%".$enterprisename."%'";
        $enterprisenolist = Doo::db()->fetchAll($query);
        $enterpriseno_str = '';
        if(!empty($enterprisenolist)){
            $enterpriseno_arr = '';
            foreach($enterprisenolist as $k=>$v){
                $enterpriseno_arr[] = $v['enterpriseno'];
            }
            $enterpriseno_str = implode(',', $enterpriseno_arr);
        }
        return $enterpriseno_str;
    }
    /*
     * 通过oa号，邮箱，手机号查找用户
     */
    public function check_user($param){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT * FROM users WHERE userno='{$param}' or email='{$param}' or mobile='{$param}'";
        $res = Doo::db()->fetchAll($query);
        if($res == NULL){
            return array('success'=>0);
        }else{
            return array('success'=>1,'info'=>$res[0]);
        }
    }
    /*
     * 查询是否绑定手机
     */
    public function check_bind($id){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT * FROM users WHERE userno='{$id}' or email='{$id}' or mobile='{$id}'";
        $is_row = Doo::db()->fetchRow($query);
        if($is_row == false){
            return array('status'=>0);
        }else{
            $info = Doo::db()->fetchAll($query);
            $mobile = $info[0]['mobile'];
            $username = $info[0]['username'];
            $userno = $info[0]['userno'];
            $email = $info[0]['email'];
            if($info[0]['isbindmobile'] == 1){
                return array(
                    'userno'=>$userno,
                    'email'=>$email,
                    'mobile'=>$mobile,
                    'username'=>$username,
                    'isbind'=>1,
                    'status'=>1
                    );
            }else{
                return array(
                    'userno'=>$userno,
                    'email'=>$email,
                    'isbind'=>0,
                    'status'=>2,
                    'mobile'=>$mobile,
                    'username'=>$username
                    );
            }
        }
    }
    /*
     * 绑定手机
     */
    public function bind_mobile($userno,$mobile){
        Doo::db()->reconnect('db_oa');
        $sql_update = "UPDATE users SET mobile='{$mobile}',isbindmobile=1,status=1101 WHERE userno=$userno";
        Doo::db()->query($sql_update);
        return 1;
    }
    /*
     * 获取套餐
     */
    public function get_all_meals(){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT mealid,name FROM system_meals WHERE status=1101 AND ispublic=0";
        $meallist = Doo::db()->fetchAll($query);
        return $meallist;
    }
    /*
     * 检查号码
     */
    public function check_mobile($userno,$mobile){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT userno,email,mobile,isbindmobile,username FROM users WHERE mobile='{$mobile}' AND userno!={$userno} LIMIT 1";
        $userlist = Doo::db()->fetchAll($query);
        if($userlist == NULL){
            $res = array('status'=>0);
            return $res;
        }else{
            $userno = $userlist[0]['userno'];
            $email = $userlist[0]['email'];
            $isbindmobile = $userlist[0]['isbindmobile'];
            $username = $userlist[0]['username'];
            $res = array(
                'status' => 1,
                'username' => $username,
                'userno' => $userno,
                'email' => $email,
                'isbindmobile' => $isbindmobile
            );
            return $res;
        }
    }
    /*
     * 清空用户的手机号码
     */
    public function empty_mobile($userno){
        Doo::db()->reconnect('db_oa');
        $query = "UPDATE users SET mobile='',isbindmobile=0,status=1101 WHERE userno=$userno";
        Doo::db()->query($query);
        return 1;
    }
    /*
     * 修改用户密码
     */
    public function alterpwd($userno,$pwd){
        Doo::loadClass('Common');
        $password = Common::encryptPassword($pwd,OA_KEY);
        Doo::db()->reconnect('db_oa');
        $query = "UPDATE users SET password='{$password}' WHERE userno={$userno}";
        Doo::db()->query($query);
        return 1;
    }
    /*
     * 用户数统计
     */
    public function applylist($param){
        Doo::db()->reconnect('db_oa');
        $query = new DoUseListQuery($param);
        $list = $query->enterpriselist;
        $res_day = $this->applybytime('day');
        $res_week = $this->applybytime('week');
        $res_month = $this->applybytime('month');
        $day_use = $this->total_use('day');
        $week_use = $this->total_use('week');
        $month_use = $this->total_use('month');
        foreach($list as $k=>$v){
            if(!empty($v['name'])){
                $namearr = $this->sub_str($v['name'],10);
                $list[$k]['name1'] = $namearr['substr'];
                $list[$k]['islong'] = $namearr['islong'];
            }else{
                $list[$k]['name1'] = '';
                $list[$k]['islong'] = 0;
            }
            $list[$k]['daytimes'] = empty($res_day[$v['enterpriseno']]) ? 0 : $res_day[$v['enterpriseno']];
            $list[$k]['weektimes'] = empty($res_week[$v['enterpriseno']]) ? 0 : $res_week[$v['enterpriseno']];
            $list[$k]['monthtimes'] = empty($res_month[$v['enterpriseno']]) ? 0 : $res_month[$v['enterpriseno']];
            $list[$k]['dayuse'] = empty($day_use[$v['enterpriseno']]) ? 0 : $day_use[$v['enterpriseno']];
            $list[$k]['weekuse'] = empty($week_use[$v['enterpriseno']]) ? 0 : $week_use[$v['enterpriseno']];
            $list[$k]['monthuse'] = empty($month_use[$v['enterpriseno']]) ? 0 : $month_use[$v['enterpriseno']];
        }
        return array('list'=>$list,'count'=>$query->count);
    }
    public function count_list($param){
        Doo::loadClass('Enum');
        $status = Enum::getStatusType('Normal');
        Doo::db()->reconnect('db_oa');
        $query = "SELECT count(distinct enterpriseno) as total FROM enterprises WHERE status={$status}";
        if(!empty($enterpriseno)){
            $query = "SELECT count(distinct enterpriseno) as total FROM enterprises WHERE status={$status} AND enterpriseno=$enterpriseno";
        }
        $list = Doo::db()->fetchAll($query);
        return $list[0]['total'];
    }
    /*
     * 获取所有企业
     */
    public function allenterprise($enterpriseno=null,$limit=null){
        Doo::loadClass('Enum');
        $status = Enum::getStatusType('Normal');
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno,name FROM enterprises WHERE status={$status}";
        if(!empty($enterpriseno)){
            $query = "SELECT enterpriseno,name FROM enterprises WHERE status={$status} AND enterpriseno=$enterpriseno";
        }
        if(!empty($limit)){
            $query .= " LIMIT ".$limit;
        }
        $list = Doo::db()->fetchAll($query);
        return $list;
    }
    /*
     * 获取企业的使用情况
     */
    public function applybytime($time){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno,SUM(operatetotal) AS operatetotal FROM system_eventlog"; 
        switch($time){
            case 'day':
                $where_str = " WHERE logdate=DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND mid!=11";
                break;
            case 'week':
                $where_str = " WHERE logdate>=DATE_SUB(CURDATE(),INTERVAL 8 DAY) AND mid!=11";
                break;
            case 'month':
                $where_str = " WHERE logdate>=DATE_SUB(CURDATE(),INTERVAL 31 DAY) AND mid!=11";
                break;
        }
        $group_str = " GROUP BY enterpriseno";
        $query .= $where_str.$group_str;
        $res = Doo::db()->fetchAll($query);
        $opreatetotal = array();
        foreach($res as $k=>$v){
            $opreatetotal[$v['enterpriseno']] = $v['operatetotal'];
        }
        return $opreatetotal;
    }
    /*
     * 使用人数统计 
     * @param opt :day 昨日 week近一周 month近一月
     */
    public function total_use($opt){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT COUNT(DISTINCT userno) AS total,enterpriseno FROM user_loginlog_fields";
        $where_str = '';
        switch ($opt){
            case 'day':
                $where_str = " WHERE logindate=DATE_SUB(CURDATE(),INTERVAL 1 DAY)";
                break;
            case 'week':
                $where_str = " WHERE logindate>=DATE_SUB(CURDATE(),INTERVAL 8 DAY)";
                break;
            case 'month':
                $where_str = " WHERE logindate>=DATE_SUB(CURDATE(),INTERVAL 31 DAY)";
                break;
        }
        $groupby = " GROUP BY enterpriseno";
        $query .= $where_str.$groupby;
        $res = Doo::db()->fetchAll($query);
        $tmp_arr = array();
        foreach($res as $k=>$v){
            $tmp_arr[$v['enterpriseno']] = $v['total'];
        }
        return $tmp_arr;
    }
    /*
     * oa.cn登录统计opt可为webtimes,iostimes,androidtimes,imtimes
     */
    public function logintotal($opt=null){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT COUNT(DISTINCT userno) AS total FROM user_loginlog";
        $where_str = '';
        if(!empty($opt)){
            $where_str = ' WHERE '.$opt.'>0';
        }
        $query .= $where_str;
        $res = Doo::db()->fetchAll($query);
        $total = $res[0]['total'];
        return $total;
    }
    /*
     * oa.cn企业数统计 
     * @param opt1:0为统计全部，1为统计正式的，2为统计试用的,opt2:1为到期，2为未到期
     * @return int total
     */
    public function enterprisetotal($opt1,$opt2=NULL){
        Doo::db()->reconnect('db_oa');
        switch ($opt1){
            case '0':
                $query = 'SELECT COUNT(DISTINCT userno) AS usertotal,COUNT(DISTINCT enterpriseno) AS entotal FROM enterprise_user LIMIT 1';
                break;
            case '1':
                $query = 'SELECT COUNT(DISTINCT a.userno) AS usertotal,COUNT(DISTINCT a.enterpriseno) AS entotal FROM enterprise_user a INNER JOIN system_license b 
                        ON a.enterpriseno=b.consumer AND b.type=0 LIMIT 1';
                break;
            case '2':
                $query = 'SELECT COUNT(DISTINCT a.userno) AS usertotal,COUNT(DISTINCT a.enterpriseno) AS entotal FROM enterprise_user a INNER JOIN system_license b 
                        ON a.enterpriseno=b.consumer AND b.type=1 LIMIT 1';
                if(!empty($opt2)){
                    if($opt2 == '1'){
                        $query = 'SELECT COUNT(DISTINCT a.userno) AS usertotal,COUNT(DISTINCT a.enterpriseno) AS entotal FROM enterprise_user a INNER JOIN system_license b 
                                    ON a.enterpriseno=b.consumer AND b.type=1 AND b.endtime<CURDATE() LIMIT 1';
                    }else{
                        $query = 'SELECT COUNT(DISTINCT a.userno) AS usertotal,COUNT(DISTINCT a.enterpriseno) AS entotal FROM enterprise_user a INNER JOIN system_license b 
                                    ON a.enterpriseno=b.consumer AND b.type=1 AND b.endtime>=CURDATE() LIMIT 1';
                    }
                }
                break;
        }
        $res = Doo::db()->fetchAll($query);
        $usertotal = $res[0]['usertotal'];
        $entotal = $res[0]['entotal'];
        return array('usertotal'=>$usertotal,'entotal'=>$entotal);
    }
    /*
     * oa.cn昨日新增的正式或体验用户
     * @param $type 1正式 2体验 3全部新增
     */
    public function yesterday_add($type){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT COUNT(DISTINCT a.userno) AS usertotal,COUNT(DISTINCT b.consumer) AS entotal 
                    FROM enterprise_user a INNER JOIN system_license b 
                  ON a.enterpriseno=b.consumer AND DATE_FORMAT(b.createtime,'%X-%m-%d')=DATE_SUB(CURDATE(),INTERVAL 1 DAY)";
        $where_str = '';
        switch($type){
            case '1':
                $where_str = " AND b.type=0";
                break;
            case '2':
                $where_str = " AND b.type=1";
                break;
            case '3':
                break;
        }
        $query .= $where_str; 
        $res = Doo::db()->fetchAll($query);
        return $res;
    }
    /*
     * 将企业端的数据传送到db_oa
     * 
     */
    public function enterprise_to_oa(){
        $query = "SELECT enterpriseno,idtype,DATE_FORMAT(createtime,'%X-%m-%d') AS createdate,COUNT(DISTINCT userno) AS usertotal,COUNT(eventlogid) as total FROM enterprise_eventlog
                    WHERE DATE_FORMAT(createtime,'%X-%m-%d')!=CURDATE() AND status=0 AND DATE_FORMAT(createtime,'%X-%m-%d')>DATE_SUB(CURDATE(),INTERVAL 7 DAY)
                    GROUP BY enterpriseno,idtype,createdate";
        $res = Doo::db()->fetchAll($query);
        $countuser = "SELECT COUNT(employeeid) AS total,enterpriseno FROM enterprises_employee GROUP BY enterpriseno";
        $countlist = Doo::db()->fetchAll($countuser);
        foreach($countlist as $k=>$v){
            $usertotal[$v['enterpriseno']] = $v['total'];
        }
        foreach($res as $k=>$v){
            $res[$k]['totalstaff'] = empty($usertotal[$v['enterpriseno']]) ? 0 : $usertotal[$v['enterpriseno']];
        }
        return $res;
    }
    /*
     * 统计公司每个模块的使用人数
     */
    public function module_user(){
        $singleuser = "SELECT enterpriseno,idtype,COUNT(DISTINCT userno) AS usertotal,MAX(DATE_FORMAT(createtime,'%X-%m-%d')) as logdate FROM enterprise_eventlog
                        WHERE DATE_FORMAT(createtime,'%X-%m-%d')!=CURDATE() 
                        GROUP BY enterpriseno,idtype";
        $singlelist = Doo::db()->fetchAll($singleuser);
        return $singlelist;
    }
    /*
     * 将数据成功插入到db_oa的user_eventlog后,标识一下
     */
    public function mark_status(){
        $query = "UPDATE enterprise_eventlog SET status=1101 WHERE DATE_FORMAT(createtime,'%X-%m-%d')!=CURDATE() AND DATE_FORMAT(createtime,'%X-%m-%d')>DATE_SUB(CURDATE(),INTERVAL 7 DAY)";
        Doo::db()->query($query);
    }
    /*
     * 汇总日志，成功后更改状态
     */
    public function total_log(){
        Doo::loadClass('OAClient');
        $res = $this->enterprise_to_oa();
        $time = date("h:i:s");
        if($res == null || $time < '05:00:00'){
            //modify by james.ou 4-7
            //定时计划任务里面不能用 exit退出，如果用exit退出后，造成调用函数，次方法后面的程序，不执行.
            //exit;
            return;
        }else{
            $singlelist = $this->module_user();
            $json = json_encode($res);
            $sign = json_encode($singlelist);
            $json_data = OAClient::logtotal_oa($json);
            $sueccess = OAClient::module_signle_user($sign);
            $this->mark_status();
        }
    }
    /*
     * 公司各模块使用情况统计
     */
    public function module_use($enterpriseno=null){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT DISTINCT mid,SUM(operatetotal) AS total FROM system_eventlog";
        $where_str = '';
        if(!empty($enterpriseno)){
            $where_str = " WHERE enterpriseno={$enterpriseno}";
        }
        $groupby = " GROUP BY mid";
        $query .= $where_str.$groupby; 
        $res = Doo::db()->fetchAll($query);
        return $res;
    }
    /*
     * 公司的累计使用人数
     */
    public function member_use($enterpriseno){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT COUNT(DISTINCT userno) AS total FROM user_loginlog_fields WHERE enterpriseno={$enterpriseno} LIMIT 1";
        $res = Doo::db()->fetchAll($query);
        return $res[0]['total'];
    }
    /*
     * 统计某个公司的人数
     */
    public function member_total($enterpriseno){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT COUNT(DISTINCT userno) AS total FROM enterprise_user WHERE enterpriseno={$enterpriseno} LIMIT 1";
        $res = Doo::db()->fetchAll($query);
        return $res[0]['total'];
    }
    /*
     * 公司各模块使用累计统计
     */
    public function enterprise_module($enterpriseno){
        Doo::loadClass('Enum');
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno,mid,SUM(operatetotal) AS operatetotal FROM system_eventlog where enterpriseno={$enterpriseno} AND mid!=11 GROUP BY mid";
        $module_use = Doo::db()->fetchAll($query);
        $operate = array();
        foreach ($module_use as $k=>$v){
            $operate[$v['mid']] = $v['operatetotal'];
        }
        return $operate;
    }
    /*
     * oa.cn模块使用情况
     */
    public function oa_module(){
        Doo::loadClass('Enum');
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno,mid,SUM(operatetotal) AS operatetotal FROM system_eventlog where  mid!=11 GROUP BY mid";
        $module_use = Doo::db()->fetchAll($query);
        foreach ($module_use as $k=>$v){
            $module_use[$k]['modulename'] = Enum::getSyslogType($v['mid']);
        }
        return $module_use;
    }
    /*
     * oa.cn最近7天登录统计
     */
    public function login_day_line($opt=null){
        Doo::db()->reconnect('db_oa');
        $query = 'SELECT count(*) as total,logindate FROM user_loginlog';
        $where_str = " WHERE UNIX_TIMESTAMP(logindate)>UNIX_TIMESTAMP(CURDATE())-604800";
        if(!empty($opt)){
            $where_str .= ' AND '.$opt.'>0';
        }
        $other_str = ' GROUP BY logindate';
        $query .= $where_str.$other_str;
        $list = Doo::db()->fetchAll($query);
        return $list;
    }
    /*
     * 公司使用过得模块
     */
    public function get_enterprise_module($enterpriseno){
        Doo::db()->reconnect('db_oa');
        Doo::loadClass('Enum');
        $query = "SELECT mid FROM system_eventlog WHERE mid!=11 AND enterpriseno=$enterpriseno GROUP BY mid";
        $module_use = Doo::db()->fetchAll($query);
        foreach ($module_use as $k=>$v){
            $module_use[$k]['modulename'] = Enum::getSyslogType($v['mid']);
        }
        return $module_use;
    }
    /*
     * 获取一个日期的前7天
     * @param string $date
     * @return array 7day
     */
    public function get_near_7day($date=null){
        $showtime = '';
        if(!empty($date)){
            $showtime = strtotime($date);
        }else{
            $showtime = time();
        }
        $date_time = array();
        $time = array();
        $i = 30;
        for($i=30;$i>0;$i--){
            $runtime = $showtime - 86400*$i;
            $date_time[] = date('m-d',$runtime);
            $time[] = date('Y-m-d',$runtime);
        }
        return array('time'=>$date_time,'time1'=>$time);
    }
    /*
     * 获取一个日期的最近4周
     */
    public function get_near_4week($date=null){
        $showtime = '';
        if(!empty($date)){
            $showtime = strtotime($date);
        }else{
            $showtime = time();
        }
        $week_xlabels = array(
            date('m-d',$showtime-86400*28).'至'.date('m-d',$showtime-86400*22),
            date('m-d',$showtime-86400*21).'至'.date('m-d',$showtime-86400*15),
            date('m-d',$showtime-86400*14).'至'.date('m-d',$showtime-86400*8),
            date('m-d',$showtime-86400*7).'至'.date('m-d',$showtime),
        );
        $week_time = array(
            array(date('Y-m-d',$showtime-86400*28),date('Y-m-d',$showtime-86400*22)),
            array(date('Y-m-d',$showtime-86400*21),date('Y-m-d',$showtime-86400*15)),
            array(date('Y-m-d',$showtime-86400*14),date('Y-m-d',$showtime-86400*8)),
            array(date('Y-m-d',$showtime-86400*7),date('Y-m-d',$showtime))
        );
        return array('week_xlabels'=>$week_xlabels,'week_time'=>$week_time);
    }
    /*
     * 获取当年的12个月
     */
    public function get_month($date=null){
        $showtime = '';
        if(!empty($date)){
            $showtime = strtotime($date);
        }else{
            $showtime = time();
        }
        $year = date('Y',$showtime);
        $month_arr = array();
        $i = 1;
        for($i = 1;$i<10;$i++){
            $month_arr[] = $year.'-0'.$i;
        }
        $month_arr[] = $year.'-10';
        $month_arr[] = $year.'-11';
        $month_arr[] = $year.'-12';
        return $month_arr;
    }
    /*
     * 中文截取字符串
     * @param string utf-8 $str 要截取的中文
     * @param int $len 要截取的长度
     * @return array 返回截取后的串，和是否大于要截取的长度
     */
    public function sub_str($str,$len){
        $str_len = mb_strlen($str,'utf-8');
        if($str_len > $len){
            $str1 = mb_substr($str,0,$len,'utf-8');
            $islong = 1;
        }else{
            $str1 = $str;
            $islong = 0;
        }
        $arr = array('substr'=>$str1,'islong'=>$islong);
        return $arr;
    }
    /*
     * oa.cn统计最近7天登录
     */
    public function login_count($time_arr,$type=null){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? ',' : '';
            if(empty($type)){
                $query .= $query_sp."(SELECT COUNT(userno) FROM user_loginlog WHERE logindate='".$v."' LIMIT 1) as '".$v."'";
            }else{
                $query .= $query_sp."(SELECT COUNT(userno) FROM user_loginlog WHERE logindate='".$v."' AND ".$type.">0 LIMIT 1) as '".$v."'";
            }
        }
        $count_query = 'SELECT '.$query.' from user_loginlog LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $count_arr[] = intval($v); 
        }
        return $count_arr;
    }
    /*
     * oa.cn统计最近4周登录
     */
    public function login_count_4week($time_arr,$type=null){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? ',' : '';
            if(empty($type)){
                $query .= $query_sp."(SELECT COUNT(DISTINCT userno) FROM user_loginlog WHERE logindate>='".$v[0]."' AND logindate<='".$v[1]."' LIMIT 1) as '".$v[0]."'";
            }else{
                $query .= $query_sp."(SELECT COUNT(DISTINCT userno) FROM user_loginlog WHERE logindate>='".$v[0]."' AND logindate<='".$v[1]."' AND ".$type.">0 LIMIT 1) as '".$v[0]."'";
            }
        }
        $count_query = 'SELECT '.$query.' from user_loginlog LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $count_arr[] = intval($v); 
        }
        return $count_arr;
    }
    /*
     * oa.cn统计月登录
     */
    public function login_count_month($time_arr,$type=null){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? ',' : '';
            if(empty($type)){
                $query .= $query_sp."(SELECT COUNT(DISTINCT userno) FROM user_loginlog WHERE DATE_FORMAT(logindate,'%X-%m')='".$v."' LIMIT 1) as '".$v."'";
            }else{
                $query .= $query_sp."(SELECT COUNT(DISTINCT userno) FROM user_loginlog WHERE DATE_FORMAT(logindate,'%X-%m')='".$v."' AND ".$type.">0 LIMIT 1) as '".$v."'";
            }
        }
        $count_query = 'SELECT '.$query.' from user_loginlog LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $count_arr[] = intval($v); 
        }
        return $count_arr;
    }
    /*
     * oa.cn7天的用户增长数据
     */
    public function user_add_7day($time_arr){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            $query .= $query_sp."(SELECT COUNT(userno) FROM users WHERE DATE_FORMAT(registertime,'%X-%m-%d')='".$v."' LIMIT 1) as '".$v."'";
        }
        $count_query = 'SELECT '.$query.' from users LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach ($count_list[0] as $k=>$v){
            $count_arr[] = intval($v);
        }
        return $count_arr;
    }
    /*
     * oa.cn4周的用户数增长数据
     */
    public function user_add_4week($time_arr){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            $query .= $query_sp."(SELECT COUNT(userno) FROM users WHERE DATE_FORMAT(registertime,'%X-%m-%d')>='".$v[0]."' AND DATE_FORMAT(registertime,'%X-%m-%d')<='".$v[1]."' LIMIT 1) as '".$v[0]."'";
        }
        $count_query = 'SELECT '.$query.' from users LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach ($count_list[0] as $k=>$v){
            $count_arr[] = intval($v);
        }
        return $count_arr;
    }
    /*
     * oa.cn用户数月增长数据
     */
    public function user_add_month($time_arr){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            $query .= $query_sp."(SELECT COUNT(userno) FROM users WHERE DATE_FORMAT(registertime,'%X-%m')='".$v."' LIMIT 1) as '".$v."'";
        }
        $count_query = 'SELECT '.$query.' from users LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach ($count_list[0] as $k=>$v){
            $count_arr[] = intval($v);
        }
        return $count_arr;
    }
    /*
     * oa.cn7天企业增长数据
     */
    public function enterprise_add_7day($time_arr){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            $query .= $query_sp."(SELECT COUNT(enterpriseno) FROM enterprises WHERE createtime='".$v."' LIMIT 1) as '".$v."'";
        }
        $count_query = 'SELECT '.$query.' from enterprises LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach ($count_list[0] as $k=>$v){
            $count_arr[] = intval($v);
        }
        return $count_arr;
    }
    /*
     * oa.cn4周企业增长数据
     */
    public function enterprise_add_4week($time_arr){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            $query .= $query_sp."(SELECT COUNT(enterpriseno) FROM enterprises WHERE createtime>='".$v[0]."' AND createtime<='".$v[1]."' LIMIT 1) as '".$v[0]."'";
        }
        $count_query = 'SELECT '.$query.' from enterprises LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach ($count_list[0] as $k=>$v){
            $count_arr[] = intval($v);
        }
        return $count_arr;
    }
    /*
     * oa.cn企业月增长数数据
     */
    public function enterprise_add_month($time_arr){
        Doo::db()->reconnect('db_oa');
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            $query .= $query_sp."(SELECT COUNT(enterpriseno) FROM enterprises WHERE DATE_FORMAT(createtime,'%X-%m')='".$v."' LIMIT 1) as '".$v."'";
        }
        $count_query = 'SELECT '.$query.' from enterprises LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach ($count_list[0] as $k=>$v){
            $count_arr[] = intval($v);
        }
        return $count_arr;
    }
    /*
     * 获取有日志的模块
     */
    public function get_logmodule($enterpriseno=null){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT DISTINCT mid FROM system_eventlog";
        if(!empty($enterpriseno)){
            $query .= " WHERE enterpriseno=$enterpriseno";
        }
        $midlist = Doo::db()->fetchAll($query);
        return $midlist;
    }
    /*
     * 使用率统计模块使用情况列表
     */
    public function module_use_list(){
        $event = new EventAction();
        $moduleinstall = $this->module_install();
        $event->Cfun_module_distinct();
        $list = $this->oa_module();
        $module = $this->sum_moduleuse_enterprise();
        foreach($list as $k=>$v){
            $list[$k]['enterprisetotal'] = $module[$v['mid']];
            $list[$k]['singleuser'] = $event->module_user_total[$v['mid']];
            $list[$k]['present'] = $event->moduel_use_present[$v['mid']];
            $list[$k]['install'] = empty($moduleinstall[$v['mid']]) ? 0 : $moduleinstall[$v['mid']];
        }
        return $list;
    }
    public function module_install(){
        Doo::db()->reconnect('db_oa');
        Doo::loadClass('Enum');
        $query = "select COUNT(DISTINCT enterpriseno) as total,modulecode FROM enterprise_module GROUP BY modulecode";
        $list = Doo::db()->fetchAll($query);
        foreach($list as $k=>$v){
            $mid = Enum::moduleToSyslogType(strtolower($v['modulecode']));
            $moduleinstall[$mid] = $v['total'];
        }
        return $moduleinstall;
    }
    /*
     * 获取各模块企业使用数
     */
    public function sum_moduleuse_enterprise(){
        Doo::db()->reconnect('db_oa');
        $query = 'SELECT mid,COUNT(DISTINCT enterpriseno) AS total FROM system_eventlog GROUP BY mid';
        $list = Doo::db()->fetchAll($query);
        foreach($list as $k=>$v){
            $module[$v['mid']] = $v['total'];
        }
        return $module;
    }
    /*
     * 各模块总体使用人数
     */
    public function sum_moduleuse_user(){
        Doo::db()->reconnect('db_oa');
        
    }
    /*
     * 获取某模块7天的使用次数
     * @param mid:模块号 type 0为统计使用次数 1为统计使用人数
     */
    public function times_near_7day($mid=null,$time_arr,$type){
        Doo::db()->reconnect('db_oa');
        if($type == 1){
            $sum = 'usertotal';
        }else{
            $sum = 'operatetotal';
        }
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            if(!empty($mid)){
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where mid=$mid AND logdate='".$v."' LIMIT 1) AS '".$v."'";
            }else{
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where logdate='".$v."' LIMIT 1) AS '".$v."'";
            }
        }
        $count_query = 'SELECT '.$query.' FROM system_eventlog LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $count_arr[] = intval($v); 
        }
        return $count_arr;
    }
    /*
     * 获取某模块4周的使用次数
     */
    public function times_4week($mid=null,$time_arr,$type){
        Doo::db()->reconnect('db_oa');
        if($type == 1){
            $sum = 'usertotal';
        }else{
            $sum = 'operatetotal';
        }
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            if(!empty($mid)){
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where mid=$mid AND logdate>='".$v[0]."' AND logdate<='".$v[1]."' LIMIT 1) AS '".$v[0]."'";
            }else{
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where logdate>='".$v[0]."' AND logdate<='".$v[1]."' LIMIT 1) AS '".$v[0]."'";
            }
        }
        $count_query = 'SELECT '.$query.' FROM system_eventlog LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $count_arr[] = intval($v); 
        }
        return $count_arr;
    }
    /*
     * 获取模块月使用次数
     */
    public function times_month($mid=null,$time_arr,$type){
        Doo::db()->reconnect('db_oa');
        if($type == 1){
            $sum = 'usertotal';
        }else{
            $sum = 'operatetotal';
        }
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            if(!empty($mid)){
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where mid=$mid AND DATE_FORMAT(logdate,'%X-%m')='".$v."' LIMIT 1) AS '".$v."'";
            }else{
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where DATE_FORMAT(logdate,'%X-%m')='".$v."' LIMIT 1) AS '".$v."'";
            }
        }
        $count_query = 'SELECT '.$query.' FROM system_eventlog LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $count_arr[] = intval($v); 
        }
        return $count_arr;
    }
    /*
     * 某公司模块使用情况
     */
    public function enterprise_module_day($enterpriseno,$mid=null,$time_arr,$type){
        Doo::db()->reconnect('db_oa');
        if($type == '1'){
            $sum = 'usertotal';
        }else{
            $sum = 'operatetotal';
        }
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            if(!empty($mid)){
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where enterpriseno=$enterpriseno AND mid=$mid AND logdate='".$v."' LIMIT 1) AS '".$v."'";
            }else{
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where enterpriseno=$enterpriseno AND logdate='".$v."' LIMIT 1) AS '".$v."'";
            }
        }
        $count_query = 'SELECT '.$query.' FROM system_eventlog LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $count_arr[] = intval($v); 
        }
        return $count_arr;
    }
    public function enterprise_module_week($enterpriseno,$mid=null,$time_arr,$type){
        Doo::db()->reconnect('db_oa');
        if($type == 1){
            $sum = 'usertotal';
        }else{
            $sum = 'operatetotal';
        }
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            if(!empty($mid)){
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where enterpriseno=$enterpriseno AND mid=$mid AND logdate>='".$v[0]."' AND logdate<='".$v[1]."' LIMIT 1) AS '".$v[0]."'";
            }else{
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where enterpriseno=$enterpriseno AND logdate>='".$v[0]."' AND logdate<='".$v[1]."' LIMIT 1) AS '".$v[0]."'";
            }
        }
        $count_query = 'SELECT '.$query.' FROM system_eventlog LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $count_arr[] = intval($v); 
        }
        return $count_arr;
    }
    public function enterprise_module_month($enterpriseno,$mid=null,$time_arr,$type){
         Doo::db()->reconnect('db_oa');
        if($type == 1){
            $sum = 'usertotal';
        }else{
            $sum = 'operatetotal';
        }
        $query = '';
        foreach($time_arr as $k=>$v){
            $query_sp = strlen($query) ? "," : '';
            if(!empty($mid)){
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where enterpriseno=$enterpriseno AND mid=$mid AND DATE_FORMAT(logdate,'%X-%m')='".$v."' LIMIT 1) AS '".$v."'";
            }else{
                $query .= $query_sp."(SELECT SUM($sum) FROM system_eventlog where enterpriseno=$enterpriseno AND DATE_FORMAT(logdate,'%X-%m')='".$v."' LIMIT 1) AS '".$v."'";
            }
        }
        $count_query = 'SELECT '.$query.' FROM system_eventlog LIMIT 1';
        $count_list = Doo::db()->fetchAll($count_query);
        foreach($count_list[0] as $k=>$v){
            $count_arr[] = intval($v); 
        }
        return $count_arr;
    }
    /*
     * 获取公司安装的模块
     */
    public function enterprise_install_module($enterpriseno){
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        Doo::db()->reconnect('db_oa');
        $adminapp = "'appadmin','UpdateLog','Userdata','MessageAdmin','Helpadmin','License','WingSaleAdmin','WingSale'";
        $query = "SELECT DISTINCT modulecode,modulename FROM enterprise_module WHERE enterpriseno=$enterpriseno AND modulecode NOT IN ($adminapp) AND status=$normal";
        $codelist = Doo::db()->fetchAll($query);
        $total = "SELECT SUM(operatetotal) AS total FROM system_eventlog WHERE enterpriseno=$enterpriseno AND mid!=11 LIMIT 1";
        $total_res = Doo::db()->fetchAll($total);
        $totalall = $total_res[0]['total'];
        $operate = $this->enterprise_module($enterpriseno);
        foreach ($codelist as $k=>$v){
            $codelist[$k]['mid'] = Enum::moduleToSyslogType(strtolower($v['modulecode']));
        }
        foreach($codelist as $k=>$v){
            $codelist[$k]['operatetotal'] = empty($operate[$v['mid']]) ? 0 : $operate[$v['mid']];
            if(!empty($totalall)){
                $present = empty($operate[$v['mid']]) ? 0 : ($operate[$v['mid']] * 100) / $totalall;
                $codelist[$k]['present'] = number_format($present,2);
            }else{
                $codelist[$k]['present'] = 0;
            }
            
        }
        return $codelist;
    }
}
/*
 * system_eventlog
 */
class EventAction{
    private $enterprise_arr = array(); //有日志的公司
    private $module_arr = array();  //有日志的模块
    public $module_user_total = array(); //模块的使用人数
    private $oa_user_total = 0;     //oa.cn总用户数
    public $moduel_use_present = array(); //oa.cn各模块的使用率
    public $infomodule = array();  //公司使用最多和最少模块
    public $moduleEnterpriseUse = array();  //公司某模块各天使用人数

    public function Cfun_module_distinct(){
        Doo::loadClass('UserDatachart');
        $this->module_distinct();
        $this->module_distinct_enterprise();
        $this->module_distinct_user();
        $usertotal = oa_user_total();
        $this->module_use_present($usertotal);
    }
    /*
     * 计算模块使用率
     */
    public function module_use_present($usertotal){
        $this->oa_user_total = $usertotal;
        foreach ($this->module_user_total as $k=>$v){
            $present = ($v * 100) / $this->oa_user_total;
            $this->moduel_use_present[$k] = number_format("$present", 2);;
        }
    }
    /*
     * 统计模块使用人数 DISTINCT
     */
    public function module_distinct_user(){
        Doo::db()->reconnect('db_oa');
        $total = '';
        foreach($this->module_arr as $k=>$v){
            $select = '';
            foreach ($this->enterprise_arr as $key=>$value){
                $select_sp = strlen($select) ? ',' : '';
                $select .= $select_sp."(SELECT MAX(singleuser) FROM system_eventlog WHERE enterpriseno=$value AND mid=$v LIMIT 1) AS '{$value}'";
            }
            $query = "SELECT ".$select." FROM system_eventlog LIMIT 1";
            $totallist = Doo::db()->fetchAll($query);
            foreach ($totallist[0] as $a=>$b){
                $total += $b;
            }
            $this->module_user_total[$v] = $total;
        }
    }
    /*
     * 统计日志公司
     */
    public function module_distinct_enterprise(){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT DISTINCT enterpriseno FROM system_eventlog";
        $enterpriselist = Doo::db()->fetchAll($query);
        foreach($enterpriselist as $k=>$v){
            $this->enterprise_arr[] = $v['enterpriseno'];
        }
    }
    /*
     * 统计日志模块
     */
    public function module_distinct(){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT DISTINCT mid FROM system_eventlog";
        $modulelist = Doo::db()->fetchAll($query);
        foreach($modulelist as $k=>$v){
            $this->module_arr[] = $v['mid'];
        }
    }
    /*
     * 找出企业使用最多和最少的模块
     */
    public function enterprise_module_focus($enterpriseno){
        Doo::db()->reconnect('db_oa');
        Doo::loadClass('Enum');
        $sub_select = "(SELECT mid FROM system_eventlog WHERE enterpriseno=$enterpriseno AND mid!=11 GROUP BY mid ORDER BY SUM(operatetotal) DESC LIMIT 1) AS max,
                        (SELECT mid FROM system_eventlog WHERE enterpriseno=$enterpriseno AND mid!=11 GROUP BY mid ORDER BY SUM(operatetotal) ASC LIMIT 1) AS min";
        $query = "SELECT ".$sub_select." FROM system_eventlog LIMIT 1";
        $list = Doo::db()->fetchAll($query);
        foreach($list[0] as $k=>$v){
            $this->infomodule[$k] = Enum::getSyslogType($v) == -1 ? '' : Enum::getSyslogType($v);
        }
    }
    /*
     * 企业30天每天的使用人数
     */
    public function enterprise_module_use_day($enterpriseno,$mid,$time_arr){
        Doo::db()->reconnect('db_oa');
        $select = '';
        foreach ($time_arr as $k=>$v){
            $select_sp = strlen($select) ? ',' : '';
            $select .= $select_sp."(SELECT usertotal FROM system_eventlog WHERE enterpriseno=$enterpriseno and logdate='{$v}' AND mid=$mid LIMIT 1) AS '{$v}'";
        }
        $query = "SELECT ".$select." FROM system_eventlog LIMIT 1";
        $list = Doo::db()->fetchAll($query);
        foreach($list[0] as $a=>$b){
            $this->moduleEnterpriseUse[] = $b;
        }
    }
    /*
     * oa.cn各模块使用率
     */
    public function oa_module_present(&$present,$mid,$time_arr,$type){
        Doo::db()->reconnect('db_oa');
        if($mid == 'all') $mid = 11;
        $sub_select = "(SELECT (SUM(usertotal)*100/SUM(totalstaff)) FROM system_eventlog WHERE mid=$mid";
        $select = '';
        switch ($type){
            case 'day':
                foreach($time_arr as $v){
                    $select_sp = strlen($select) ? ',' : '';
                    $select .= $select_sp.$sub_select." AND logdate='{$v}' LIMIT 1) AS '{$v}'";
                }
                break;
            case 'week':
                foreach($time_arr as $v){
                    $select_sp = strlen($select) ? ',' : '';
                    $select .= $select_sp.$sub_select." AND logdate>='{$v[0]}' AND logdate<='{$v[1]}' LIMIT 1) AS '{$v}'";
                }
                break;
            case 'month':
                foreach($time_arr as $v){
                    $select_sp = strlen($select) ? ',' : '';
                    $select .= $select_sp.$sub_select." AND DATE_FORMAT(logdate,'%X-%m')='{$v}' LIMIT 1) AS '{$v}'";
                }
                break;
        }
        $query = "SELECT ".$select." FROM system_eventlog LIMIT 1";
        $res = Doo::db()->fetchAll($query);
        foreach ($res[0] as $k=>$v){
            $present[] = intval($v);
        }
    }
}
/*
 * enterprise_user
 */
class EnterpriseUserAction{
    public $enterpriseUserArr = array();
    /*
     * 统计最近30天 公司每天的人数
     */
    public function enterpriseUserTotalMonth($enterpriseno,$time_arr){
        Doo::db()->reconnect('db_oa');
        $select = '';
        $select_sp = '';
        foreach ($time_arr as $a=>$b){
            $select_sp = strlen($select) ? ',' : '';
            $select .= $select_sp."(SELECT COUNT(DISTINCT userno) FROM enterprise_user WHERE enterpriseno=$enterpriseno AND DATE_FORMAT(createtime,'%X-%m-%d')<='{$b}' LIMIT 1) AS '{$b}'";
        }
        $query = "SELECT ".$select." FROM enterprise_user LIMIT 1";
        $list = Doo::db()->fetchAll($query);
        foreach($list[0] as $x=>$y){
            $this->enterpriseUserArr[] = $y;
        }
    }
}
/*
 * 组合
 */
class CompositeFunction{
    private $presentArr = array();
    
    public function module_present_day($enterpriseno,$time_arr,$mid){
        Doo::db()->reconnect('db_oa');
        if($mid == 'all'){
            $mid = 11;
        }
        $select = '';
        foreach($time_arr as $k=>$v){
            $select_sp = strlen($select) ? ',' : '';
            $select .= $select_sp."(SELECT usepresent FROM system_eventlog WHERE mid=$mid AND enterpriseno=$enterpriseno AND logdate='{$v}' LIMIT 1) AS '{$v}'";
        }
        $query = "SELECT ".$select." FROM system_eventlog LIMIT 1";
        $list = Doo::db()->fetchAll($query);
        foreach($list[0] as $k=>$v){
            $this->presentArr[] = intval($v*100);
        }
        return $this->presentArr;
    }
    public function module_present_week($enterpriseno,$time_arr,$mid){
        Doo::db()->reconnect('db_oa');
        if($mid == 'all'){
            $mid = 11;
        }
        $select = '';
        foreach($time_arr as $k=>$v){
            $select_sp = strlen($select) ? ',' : '';
            $select .= $select_sp."(SELECT sum(usepresent) as total FROM system_eventlog WHERE mid=$mid AND enterpriseno=$enterpriseno AND logdate>='{$v[0]}' AND logdate<='{$v[1]}' LIMIT 1) AS '{$v[0]}'";
        }
        $query = 'SELECT '.$select.' from user_loginlog_fields LIMIT 1';
        $list = Doo::db()->fetchAll($query);
        foreach($list[0] as $k=>$v){
            $this->presentArr[] = intval($v*100/7);
        }
        return $this->presentArr;
    }
    public function module_present_month($enterpriseno,$time_arr,$mid){
        Doo::db()->reconnect('db_oa');
        if($mid == 'all'){
            $mid = 11;
        }
        $select = '';
        foreach($time_arr as $k=>$v){
            $select_sp = strlen($select) ? ',' : '';
            $select .= $select_sp."(SELECT sum(usepresent) AS total FROM system_eventlog WHERE mid=$mid AND enterpriseno=$enterpriseno AND DATE_FORMAT(logdate,'%X-%m')='{$v}' LIMIT 1) AS '{$v}'";
        }
        $query = "SELECT ".$select." FROM system_eventlog LIMIT 1";
        $list = Doo::db()->fetchAll($query);
        foreach($list[0] as $k=>$v){
            $this->presentArr[] = intval($v*100/30);
        }
        return $this->presentArr;
    }
}

class DoUseListQuery{
    private $usepresent = array();
    private $enterprisenoStr = '';
    private $param = array(); //查询条件
    private $presentday = array();  //日使用率
    private $presentweek = array(); //周使用率
    private $presentmonth = array(); //月使用率
    public $enterpriselist = array(); //符合条件的公司列表
    public $count = '';
    
    
    public function __construct($param) {
        $this->param = $param;
        if(!empty($this->param['total_max'])){
            $this->queryByUseTotal();
        }
        $this->presentday();
        $this->presentweek();
        $this->presentmonth();
        $this->queryEnterprise();
        $this->count();
    }
    private function queryByUseTotal(){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno,COUNT(DISTINCT userno) AS total FROM user_loginlog_fields GROUP BY enterpriseno";
        $list = Doo::db()->fetchAll($query);
        foreach($list as $k=>$v){
            if($v['total'] >= $this->param['total_min'] && $v['total'] < $this->param['total_max']){
                $str_sp = strlen($this->enterprisenoStr) ? ',' : '';
                $this->enterprisenoStr .= $str_sp.$v['enterpriseno'];
            }
        }
        if(empty($this->enterprisenoStr)){
            $this->enterprisenoStr = 11;
        }
    }
    private function queryEnterprise(){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno,name FROM enterprises WHERE (enterpriseno='".$this->param['enterpriseno']."' OR name LIKE '%".$this->param['enterpriseno']."%')";
        if(!empty($this->enterprisenoStr)){
            $query .= " AND enterpriseno IN (".$this->enterprisenoStr.")";
        }
        $query .= $this->param['limit'];
        $this->enterpriselist = Doo::db()->fetchAll($query);

        foreach ($this->enterpriselist as $k=>$v){
            $this->enterpriselist[$k]['presentday'] = empty($this->presentday[$v['enterpriseno']]) ? 0 : $this->presentday[$v['enterpriseno']] * 100;
            $this->enterpriselist[$k]['presentweek'] = empty($this->presentweek[$v['enterpriseno']]) ? 0 : $this->presentweek[$v['enterpriseno']] * 100;
            $this->enterpriselist[$k]['presentmonth'] = empty($this->presentmonth[$v['enterpriseno']]) ? 0 : $this->presentmonth[$v['enterpriseno']] * 100;
        }
    }
    private function presentday(){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno,usepresent FROM system_eventlog WHERE mid=11 and logdate=DATE_SUB(CURDATE(),INTERVAL 1 DAY) GROUP BY enterpriseno";
        $list = Doo::db()->fetchAll($query);
        foreach($list as $k=>$v){
            $this->presentday[$v['enterpriseno']] = $v['usepresent'];
        }
    }
    private function presentweek(){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno,SUM(usepresent)/7 AS total FROM system_eventlog WHERE mid=11 and logdate>=DATE_SUB(CURDATE(),INTERVAL 7 DAY) GROUP BY enterpriseno";
        $list = Doo::db()->fetchAll($query);
        foreach($list as $k=>$v){
            $present = $v['total'];
            $this->presentweek[$v['enterpriseno']] = number_format($present,2);
        }
    }
    private function presentmonth(){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT enterpriseno,SUM(usepresent)/30 AS total FROM system_eventlog WHERE mid=11 and logdate>=DATE_SUB(CURDATE(),INTERVAL 30 DAY) GROUP BY enterpriseno";
        $list = Doo::db()->fetchAll($query);
        foreach($list as $k=>$v){
            $present = $v['total'];
            $this->presentmonth[$v['enterpriseno']] = number_format($present,2);
        }
    }
    private function count(){
        Doo::db()->reconnect('db_oa');
        $query = $query = "SELECT COUNT(DISTINCT enterpriseno) AS total FROM enterprises WHERE enterpriseno='".$this->param['enterpriseno']."' OR name LIKE '%".$this->param['enterpriseno']."%'";
        if(!empty($this->enterprisenoStr)){
            $query .= " AND enterpriseno IN (".$this->enterprisenoStr.")";
        }
        $total = Doo::db()->fetchAll($query);
        $this->count = $total[0]['total'];
    }
}
/*
 * class for bind email
 */
class EmailBind{
    private $info = array(); //用户信息
    private $row;
    
    public function checkid($userno,$email){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT * FROM users WHERE email='{$email}' AND userno!=$userno";
        $list = Doo::db()->fetchAll($query);
        if(!empty($list)){
            return $list[0];
        }else{
            return array('userno'=>0);
        }
    }
    public function updateemail($userno,$emial){
        Doo::db()->reconnect('db_oa');
        $query = "UPDATE users SET email='{$emial}' WHERE userno=$userno";
        Doo::db()->query($query);
    }
    /*
     * 绑定邮箱
     */
    public function checkemail($userno){
        Doo::db()->reconnect('db_oa');
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        $query = "UPDATE users SET isbindemail=1,status={$normal} WHERE userno={$userno}";
        $this->row = Doo::db()->query($query);
        return $this->row;
    }
}
class MobiErrorLog{
    /*
     * 手机错误日志列表
     */
    public function mobi_error_list($limit){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT * FROM mobile_errorslog ORDER BY createtime DESC ".$limit;
        return Doo::db()->fetchAll($query);
    }
    /*
     * 手机错误日志统计
     */
    public function mobi_error_count(){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT COUNT(logid) AS total FROM mobile_errorslog LIMIT 1";
        $count = Doo::db()->fetchAll($query);
        $total = $count[0]['total'];
        return $total;
    }
    /*
     * 手机错误日志详情
     */
    public function mobi_error_detail($logid){
        Doo::db()->reconnect('db_oa');
        $query = "SELECT errorcontent FROM mobile_errorslog WHERE logid=".$logid." LIMIT 1";
        $detail = Doo::db()->fetchAll($query);
        return $detail[0]['errorcontent'];
    }
    /*
     * 删除手机错误日志
     */
    public function del_mobile_errorlog($logid){
        Doo::db()->reconnect('db_oa');
        $query = "DELETE FROM mobile_errorslog WHERE logid=".$logid;
        Doo::db()->query($query);
    }
}
?>