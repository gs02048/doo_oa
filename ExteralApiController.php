<?php
/**
 * Description of OpenApiController
 *     翼办公对信任外部应用开放接口
 * @author Administrator
 */
error_reporting(E_ALL);

class ExteralApiController extends DooController{
    /*
     * 翼办公自定义servicekey
     */
    const SERVICEKEY = '1234567890ABCDEF';
    /*
     * 操作成功
     */
    const SUCCESS_CODE = 10000;
    /*
     * 操作失败
     */
    const ERROR_CODE = 10001;
    /*
     * 请求时间超时
     */
    const TIME_OUT = 10002;
    /*
     * 平台用户不存在
     */
    const NO_USER_RECORD = 10003;
    /*
     * 哈希码错误
     */
    const HASHCODE_ERROR = 10004;
    /*
     * 参数异常
     */
    const ARGV_EX = 10005;

    private $time;
    private $request;
    private $userno;
    private $enterpriseno;
    
    public static $resultcode = array(
        '10000' => '操作成功',
        '10001' => '操作失败',
        '10002' => '请求时间超时',
        '10003' => '平台用户不存在',
        '10004' => '哈希码错误',
        '10005' => '参数异常',
        
    );
    
    public function __construct() {
        Doo::loadClass('ExteralApi');
        $this->time = time();
        header("Content-type:application/json");
        header("Content-type: application/json");
    }
    /*
     * 初始化请求数据
     * 转义请求参数
     * 检测hashcode是否正确
     */
    private function init_requestdata() {
        $data = $this->getgpc('R');
        if ($data) {
            $this->request = $data;
            $this->request = $this->daddslashes($this->request, 1, TRUE);
            //$this->check_time($this->request['timestamp']);
        }else{
            $json = '';
            $result = json_encode($json);
            $this->write_json($result);
        }
    }
    /*
     * 获取GET/POST/REQUEST过来的数据
     */
    private function getgpc($var = 'R') {
        switch ($var) {
            case 'G': $var = &$_GET;
                break;
            case 'P': $var = &$_POST;
                break;
            case 'C': $var = &$_COOKIE;
                break;
            case 'R': $var = &$_REQUEST;
                break;
        }
        return isset($var) ? $var : NULL;
    }
    /*
     * 转义提交的数据
     */
    private function daddslashes(&$string, $force = 0, $strip = FALSE) {
        if (!get_magic_quotes_gpc() || $force) {
            if (is_array($string)) {
                foreach ($string as $key => $val) {
                    $string[$key] = $this->daddslashes($val, $force, $strip);
                }
            } else {
                $string = addslashes($strip ? stripslashes($string) : $string);
            }
        }
        return $string;
    }
    /*
     * 检查请求时间是否过期
     */
    private function check_time($t){
        if($this->time - $t > 3600){        //验证链接是否超时
            $json = array('returncode'=>self::TIME_OUT);
            echo json_encode($json);
            exit;
        }
    }
    /*
     * 检测提交的hashcode是否正确
     */
    private function check_hashcode($params){
        $code = '';
        foreach ($params as $key=>$value){
            if($value != '' && $key != 'hashcode'){
                $code .= $value;
            }
        }
        $code .= self::SERVICEKEY;
        $sign = md5($code);
        if(isset($params['hashcode'])){
            $hashcode = $params['hashcode'];
            if($sign != $hashcode){       //哈希码不正确
                $json = array('returncode'=>self::HASHCODE_ERROR);
                $result = json_encode($json);
                $this->write_json($result);
            }
        }else{                          //没有传哈希码,参数异常
            $json = array('returncode'=>self::ARGV_EX);
            $result = json_encode($json);
            $this->write_json($result);
        }
    }
    /*
     * 输出json结果
     */
    private function write_json($result) {
        $callback = isset($this->request['callback']) ? $this->request['callback'] : null;
        if (isset($callback)) {
            echo $callback . '(' . $result . ');';
        } else {
            echo $result;
        }
        //打印sql
        if(isset($_GET['oa_open_debug']) && $_GET['oa_open_debug'] == 'open'){
            print_r(Doo::db()->show_sql());
        }
        exit;
    }
    /*
     * 检查不为空的参数
     */
    private function check_params($p1,$m1,$p2=1,$m2=1,$p3=1,$m3=1){
        $json = array('returncode'=>self::ARGV_EX,'content'=>FALSE);
        if(empty($p1)){
            $json['content'] = $m1;
        }else if(empty ($p2)){
            $json['content'] = $m2;
        }else if(empty ($p3)){
            $json['content'] = $m3;
        }
        if($json['content']){
            echo json_encode($json);exit;
        }
    }
    /*
     * 获取操作人的信息
     */
    private function check_user($ext_userid){
        $json = NULL;
        $u = ExteralApi::get_user_byextid($ext_userid);
        if($u == NULL){
            $json = array('returncode'=>self::NO_USER_RECORD);
        }else{
            if(empty($u->userno) || empty($u->enterpriseno)){
                $json = array('returncode'=>self::NO_USER_RECORD);
            }
        }
        if($json != NULL){
            echo json_encode($json);
            exit;
        }
        $this->userno = $u->userno;
        $this->enterpriseno = $u->enterpriseno;
    }

    //###################################################################以下部分是各操作的详细处理##########################################################################
    /*
     * 翼办公对外接口程序入口
     * 处理api请求,检测hashcode是否正确,获取相应的method方法,提交给相应的method方法处理
     */
    public function reply_api(){
        $this->init_requestdata();
        //$this->check_hashcode($this->request);
        $method = isset($this->request['method']) ? $this->request['method'] : -1;
        if($method != -1 && method_exists($this,$method)){
            $this->{$method}();
        }else{
            $json = array('returncode'=>self::ARGV_EX);
            $result = json_encode($json);
            $this->write_json($result);
        }
    }
    /*
     * 用户主数据同步
     */
    private function synuser(){
        $ext_userid = isset($this->request['ext_userid']) ? trim($this->request['ext_userid']) : '';
        $ext_username = isset($this->request['ext_username']) ? trim($this->request['ext_username']) : '';
        $ext_mobile = isset($this->request['ext_mobile']) ? trim($this->request['ext_mobile']) : '';
        $ext_email = isset($this->request['ext_email']) ? trim($this->request['ext_email']) : '';
        $res = ExteralApi::sync_user($ext_userid, $ext_username, $ext_mobile, $ext_email);
        if($res){
            $json = array('returncode'=>self::SUCCESS_CODE);
        }else{
            $json = array('returncode'=>self::NO_USER_RECORD);
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 系统管理  获取翼办公组织信息
     */
    private function orglist(){
        $ext_userid = isset($this->request['ext_userid']) ? trim($this->request['ext_userid']) : '';
        $this->check_user($ext_userid);
        $res = ExteralApi::get_org_list($this->enterpriseno);
        if($res != -1){
            $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$res);
        }else{
            $json = array('returncode'=>self::NO_USER_RECORD);
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件  发翼办公内部邮件
     */
    private function sendpms(){
        $ext_userid = isset($this->request['ext_userid']) ? trim($this->request['ext_userid']) : '';
        $title = isset($this->request['title']) ? trim($this->request['title']) : '';
        $tousers = isset($this->request['tousers']) ? trim($this->request['tousers']) : '';
        $cctousers = isset($this->request['cctousers']) ? trim($this->request['cctousers']) : '';
        $content = isset($this->request['content']) ? trim($this->request['content']) : '';
        $attachments = isset($this->request['attachments']) ? trim($this->request['attachments']) : '';
        $this->check_params($title,'邮件标题不能为空',$tousers,'收件人不能为空',$content, '邮件内容不能为空');
        $res = ExteralApi::send_pms($ext_userid, $title, $tousers, $cctousers, $content, $attachments);
        if($res){
            $json = array('returncode'=>self::SUCCESS_CODE);
        }else{
            $json = array('returncode'=>self::ERROR_CODE);
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件  翼办公内部邮件收件箱列表
     */
    private function pmslist(){
        $ext_userid = isset($this->request['ext_userid']) ? $this->request['ext_userid'] : '';
        $this->check_user($ext_userid);
        $pagesize = isset($this->request['pagesize']) ? $this->request['pagesize'] : 20;
        $pageindex = isset($this->request['pageindex']) ? $this->request['pageindex'] : 1;
        $json = ExteralApi::get_inbox_list($this->userno,$this->enterpriseno,$pagesize,$pageindex);
        $json['returncode'] = self::SUCCESS_CODE;
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 公告   获取公告列表
     */
    private function newslist(){
        $ext_userid = isset($this->request['ext_userid']) ? $this->request['ext_userid'] : '';
        $this->check_user($ext_userid);
        $pagesize = isset($this->request['pagesize']) ? $this->request['pagesize'] : 20;
        $pageindex = isset($this->request['pageindex']) ? $this->request['pageindex'] : 1;
        $json = ExteralApi::get_news_list($this->userno,  $this->enterpriseno, $pagesize, $pageindex);
        $json['returncode'] = self::SUCCESS_CODE;
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 公告   发送公告
     */
    private function sendnews(){
        $ext_userid = isset($this->request['ext_userid']) ? $this->request['ext_userid'] : '';
        $this->check_user($ext_userid);
        $title = isset($this->request['title']) ? trim($this->request['title']) : '';
        $tousers = isset($this->request['tousers']) ? trim($this->request['tousers']) : '';
        $content = isset($this->request['content']) ? trim($this->request['content']) : '';
        $type = isset($this->request['type']) ? trim($this->request['type']) : '';
        $attachments = isset($this->request['attachments']) ? trim($this->request['attachments']) : '';
        $this->check_params($title,'标题不能为空',$tousers,'收件人不能为空',$content, '内容不能为空');
        $res = ExteralApi::send_news($this->userno, $this->enterpriseno, $title, $content, $tousers, $type, $attachments);
        if($res){
            $json['returncode'] = self::SUCCESS_CODE;
        }else{
            $json['returncode'] = self::ERROR_CODE;
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 附件  上传附件
     */
    private function upload_file(){
        $ext_userid = isset($this->request['ext_userid']) ? $this->request['ext_userid'] : '';
        $this->check_user($ext_userid);
        if(!isset($_FILES['filedata']) || $_FILES['filedata'] == NULL){
            $json = array('returncode'=>  self::ERROR_CODE,'content'=>'未发现上传文件');
            echo json_encode($json);
            exit;
        }
        $tmp_name = $_FILES['filedata']['tmp_name'];
        $name = $_FILES['filedata']['name'];
        $size = $_FILES['filedata']['size'];
        $r = ExteralApi::upload_file($this->userno,$this->enterpriseno, $name, $size,$tmp_name);
        if($r){
            $json = array('returncode'=>  self::SUCCESS_CODE,'content'=>$r);
        }else{
            $json = array('returncode'=>self::ERROR_CODE,'content'=>'文件上传失败');
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 附件  下载附件
     */
    private function download_file(){
        $ext_userid = isset($this->request['ext_userid']) ? $this->request['ext_userid'] : '';
        $attachmentid = isset($this->request['attachmentid']) ? $this->request['attachmentid'] : '';
        $this->check_user($ext_userid);
        ExteralApi::download_file($attachmentid);
    }
}

?>
