<!DOCTYPE heml>
<html lang = "ja">
<head>
    <meta charset = "UFT-8">
    <title>掲示板</title>
</head>
<body>
    
    <?php
        //データベースに接続
        $dsn = "mysql:dbname=tb250142db;host=localhost";
        $user = "tb-250142";
        $password = "h8MhFPD4zh";
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
                
        //テーブル作成
        $sql = "CREATE TABLE IF NOT EXISTS BBtable (id INT AUTO_INCREMENT PRIMARY KEY, name char(32), comment TEXT, date char(32), password char(32))";
        $stmt = $pdo->query($sql);
    ?>
    
    <?php
        //編集システム(前半)
        $mode = false; //モード識別変数(false：投稿モード　true：編集モード)
        $temp_error_input = false; //エラー識別変数(未入力)
        $temp_error_data = false; //エラー識別変数(データ)
        $temp_error_password = false; //エラー識別変数(パスワード)
        
        if(isset($_POST["edit_prepare"])){ //編集ボタンが押されたら以下を実行
            if(!empty($_POST["edit_num"]) && !empty($_POST["edit_password"])){ //入力欄が全て埋まっている場合以下を実行
                
                $edit_num = $_POST["edit_num"]; //編集番号
                
                if(data_exist($edit_num, $pdo)){ //データが存在する場合以下を実行
                    
                    $edit_password = $_POST["edit_password"]; //パスワード
                    
                    if(password_match($edit_num, $edit_password, $pdo)){ //パスワードが一致していたら以下を実行
                    
                        $mode = true; //編集モードに変更
                    
                        //編集データの取り出し
                        $sql ="SELECT name, comment FROM BBtable WHERE id = :edit_num and password = :password";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(":edit_num", $edit_num, PDO::PARAM_INT);
                        $stmt->bindParam(":password", $edit_password, PDO::PARAM_INT);
                        $stmt->execute();
                        $results = $stmt->fetchAll();
                        
                        foreach($results as $row){
                            $edit_before_name = $row["name"]; //編集前の名前
                            $edit_before_comment = $row["comment"]; //編集前のコメント
                            $edit_before_password = $edit_password; //編集前のパスワード
                        }
                    }
                    else{ //パスワードが一致していなかったら以下を実行
                        $temp_error_password = true; 
                    }
                }
                else{ //データが存在しない場合以下を実行
                    $temp_error_data = true; 
                }
            }
            else{ //入力欄が埋まっていない場合以下を実行
                $temp_error_input = true;
            }
        }
    ?>
    
    <form action = "" method = "post">
        
        <h2><?php if($mode){echo "現在 " . $edit_num . "番 を編集中";} else{echo "新規投稿";} ?></h2>
        <label>
            名前：
            <br>
            <input type = "text" name = "<?php mode_change($mode, "name"); ?>" value = "<?php if($mode){ echo $edit_before_name; }?>" autocomplete = "off">
        </label>
        <br>
        <label>
            内容：
            <br>
            <input type = "text" name = "<?php mode_change($mode, "comment"); ?>" value = "<?php if($mode){ echo $edit_before_comment; }?>" autocomplete = "off">
        </label>
        <br>
        <label>
            パスワード：
            <br>
            <input type = "text" name = "post_password" value = "<?php if($mode){echo $edit_before_password;} ?>" autocomplete = "off">
        </label>
        <input type = "submit" name = "<?php if($mode){echo "edit";} else{echo "post";}?>" value = "<?php if($mode){echo "編集";} else{echo "投稿";}?>">
        
        <br>
        
        <h2>投稿削除</h2>
        <label>
            削除番号：
            <br>
            <input type = "number" name = "delete_num" autocomplete = "off">
        </label>
        <br>
        <label>
            パスワード：
            <br>
            <input type = "text" name = "delete_password" autocomplete = "off">
        </label>
        <input type = "submit" name = "delete" value = "削除">
        
        <br>
        
        <h2>投稿編集</h2>
        <label>
            編集番号：
            <br>
            <input type = "number" name = "edit_num" autocomplete = "off">
        </label>
        <br>
        <label>
            パスワード：
            <br>
            <input type = "text" name = "edit_password" autocomplete = "off">
        </label>
        <input type = "submit" name = "edit_prepare" value = "編集">
        <input type = "hidden" name = "temp_edit_num" value = "<?php if($mode){echo $edit_num;} ?>">
        
    </form>
    
    <?php
        //デフォルト
        if(!isset($_POST["post"]) && !isset($_POST["delete"]) && !isset($_POST["edit_prepare"]) && !isset($_POST["edit"])){ //どのボタンも押されていない場合
            
            output_post($pdo); //投稿を表示
            
        }
    ?>
    
    <?php
        //投稿システム
        if(isset($_POST["post"])){ //投稿ボタンが押されたら以下を実行
            if(!empty($_POST["post_name"]) && !empty($_POST["post_comment"]) && !empty($_POST["post_password"])){ //名前,コメント,パスワードが格納されていたら以下を実行
                
                $post_name = $_POST["post_name"]; //投稿する名前
                $post_comment = $_POST["post_comment"]; //投稿するコメント
                $post_date = date("Y/m/d/ H:i:s"); //投稿日時
                $post_password = $_POST["post_password"]; //パスワード
                
                //データを追加
                $sql = $pdo -> prepare("INSERT INTO BBtable (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
                $sql -> bindParam(":name", $post_name, PDO::PARAM_STR);
                $sql -> bindParam(":comment", $post_comment, PDO::PARAM_STR);
                $sql -> bindParam(":date", $post_date, PDO::PARAM_STR);
                $sql -> bindParam(":password", $post_password, PDO::PARAM_STR);
                $sql -> execute();
                
                //データの出力
                output_post($pdo);
                
            }
            else{ //未入力の欄がある場合以下を実行
                
                //データの出力
                error_massage("input");
                output_post($pdo);
                
            }
        }
        
        //削除システム
        if(isset($_POST["delete"])){ //削除ボタンが押されたら以下を実行
            if(!empty($_POST["delete_num"]) && !empty($_POST["delete_password"])){ //削除番号，パスワードが格納されていたら以下を実行
                
                $delete_num = $_POST["delete_num"]; //削除番号
                
                if(data_exist($delete_num, $pdo)){ //データが存在していたら以下を実行
                    
                    $delete_password = $_POST["delete_password"]; //パスワード
                    
                    if(password_match($delete_num, $delete_password, $pdo)){ //パスワードが一致していたら以下を実行
                        
                        //データの削除
                        $sql = "delete FROM BBtable WHERE id = :delete_num and password = :password";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(":delete_num", $delete_num, PDO::PARAM_INT);
                        $stmt->bindParam(":password", $delete_password, PDO::PARAM_STR);
                        $stmt->execute();
                    
                        //データの出力
                        output_post($pdo);
                        
                    }
                    else{ //パスワードが一致していなかったら以下を実行
                        
                        //データの出力
                        error_massage("password");
                        output_post($pdo);
                        
                    }
                }
                else{ //データが存在しない場合以下を実行
                
                    //データの出力
                    error_massage("data");
                    output_post($pdo);
                    
                }
                
            }
            else{ //未入力の欄がある場合以下を実行
                
                //データの出力
                error_massage("input");
                output_post($pdo);
                
            }
        }
        
        //編集システム(後半)
        if($temp_error_input){ //エラーメッセージ(未入力)
            error_massage("input");
            output_post($pdo);
        }
        if($temp_error_data){ //エラーメッセージ(データが存在しない)
            error_massage("data");
            output_post($pdo);
        }
        if($temp_error_password){ //エラーメッセージ(パスワードの不一致)
            error_massage("password");
            output_post($pdo);
        }
        
        if(isset($_POST["edit"])){ //編集ボタンが押された場合以下を実行
            if(!empty($_POST["edit_after_name"]) && !empty($_POST["edit_after_comment"])){ //入力欄が埋まっている場合以下を実行
                
                $edit_after_name = $_POST["edit_after_name"]; //編集後の名前
                $edit_after_comment = $_POST["edit_after_comment"]; //編集後のコメント
                $edit_date = date("Y/m/d/ H:i:s"); //編集時の日時
                
                //編集データの更新
                $sql = "UPDATE BBtable SET name = :name, comment = :comment, date = :date WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":name", $edit_after_name, PDO::PARAM_STR);
                $stmt->bindParam(":comment", $edit_after_comment, PDO::PARAM_STR);
                $stmt->bindParam(":date", $edit_date, PDO::PARAM_STR);
                $stmt->bindParam(":id", $_POST["temp_edit_num"], PDO::PARAM_INT);
                $stmt->execute();
                
                //データの出力
                output_post($pdo);
            
            } 
            else{ //入力欄が埋まっていない場合以下を実行
            
                //データの出力
                error_massage("input");
                output_post($pdo);
                
            }
        }
    ?>
    
    <?php
        //モード変更関数
        function mode_change($mode, $name){
            if($mode){
                if($name == "name"){
                    echo "edit_after_name";
                }
                else{
                    echo "edit_after_comment";
                }
            }
            else{
                if($name == "name"){
                    echo "post_name";
                }
                else{
                    echo "post_comment";
                }
            }
        }
    ?>
    
    <?php
        //投稿を表示する関数
        function output_post($pdo){
            echo "<br>";
            echo "<h2> 投稿件数(" . get_total_post_num($pdo) . "件)</h2>";
            
            $sql = "SELECT * FROM BBtable";
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                echo $row["id"] . ": " . $row["name"] . " " . $row["date"] . "<br>";
                echo $row["comment"].'<br>';
                echo "<hr>";
            }
        }
    ?>
    
    <?php
        //全投稿数を取得する関数
        function get_total_post_num($pdo){
            $sql = "SELECT id FROM BBtable";
            $stmt = $pdo->query($sql);
            $count = $stmt->rowCount();
            
            return $count;
        }
    ?>
    
    <?php
        //エラーメッセージを表示する関数
        function error_massage($problem){
            
            if($problem == "input"){ //未入力
                echo "<font color = red>＊未入力の欄があります</font>";
            }
            elseif($problem == "data"){ //データが存在しない
                echo "<font color = red>＊指定した番号のデータが存在しません</font>";
            }
            else{ //パスワードが一致しない
                echo "<font color = red>＊パスワードが一致していません</font>";
            }
            
        }
    ?>
    
    <?php
        //指定した番号のデータが存在するか判断する関数
        function data_exist($num, $pdo){
            
            $sql = "SELECT id FROM BBtable WHERE id = :num";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":num", $num, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll();
                
            if(!empty($result[0])){
                return true;
            }
            else{
                return false;
            }
        }
    ?>
    
    <?php
        //パスワードが一致するか判断する関数
        function password_match($num, $password, $pdo){
            
            $sql = "SELECT id FROM BBtable WHERE id = :num and password = :password";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":num", $num, PDO::PARAM_INT);
            $stmt->bindParam(":password", $password, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll();
                
            if(!empty($result[0])){
                return true;
            }
            else{
                return false;
            }
        }
    ?>
    
</body>
</html>