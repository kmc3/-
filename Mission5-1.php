<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>掲示板</title>
</head>
<body>
	<h1>掲示板</h1>
    
    <?php

	//変数の定義
        $deleteNo = ''; // 削除する投稿番号
        $editNo = ''; // 編集する投稿番号
        $prePassword = ''; // 編集前のパスワード
        $preName = ''; // 編集前の名前
        $preComment = ''; // 編集前のコメント

	// データベース名、ユーザー名、パスワード
	$dsn = 'データベース名';
	$user = 'ユーザー名';
	$password = 'パスワード';

	//MySQLに接続
	try{
		//PDOという方法で接続する
		$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
		//keijibanという名前のテーブルを作成
		$sql = "CREATE TABLE IF NOT EXISTS keijiban"
		//カラムの作成
		." ("
		. "id INT AUTO_INCREMENT PRIMARY KEY,"	//投稿番号
		. "name char(32),"		//名前
		. "comment TEXT,"		//コメント
		. "datetime DATETIME,"	//日付
		. "password char(32)"	//パスワード
		.");";
		$stmt = $pdo->query($sql);

		//投稿機能
		//名前、コメント、パスワードが入力されていて編集が入力されていない場合新規投稿として処理
		if (!empty($_POST['comment']) && !empty($_POST['name'])){
			if (empty ($_POST['editPostNo'])&& !empty($_POST['pass'])){
				// prepareメソッドでデータの入力をする
				//テーブルのカラムに対してパラメータを与える
				$sql = $pdo -> prepare("INSERT INTO keijiban (name, comment, datetime, password) VALUES (:name, :comment, :datetime, :password)");
				// これらのパラメータを文字列として指定
				$sql -> bindParam(':name', $name, PDO::PARAM_STR);
				$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
				$sql -> bindParam(':datetime', $datetime, PDO::PARAM_STR);
				$sql -> bindParam(':password', $password, PDO::PARAM_STR);
				//その他の定義
				$name = $_POST['name'];	//投稿者の名前
				$comment = $_POST['comment'];	//投稿されたコメント
				$datetime = date("Y/m/d H:i:s");	//日付
				$password = $_POST['pass'];	//パスワード
				//これらを実行する
				$sql -> execute();
					   
			//editPostNoとパスワードが入力されている場合、編集として処理
			}elseif (!empty($_POST['editPostNo']) && !empty($_POST['pass'])){
				$name = $_POST['name'];	
				$comment = $_POST['comment'];	
				$datetime = date("Y/m/d H:i:s");	
				$password = $_POST['pass'];	
				$id = $_POST['editPostNo'];	//隠れてる編集番号
				//SQL文　テーブルから編集対象番号が一致するものをアップデートする
				$sql = "UPDATE keijiban SET name=:name,comment=:comment,datetime=:datetime,password=:password WHERE id=:id";
				//prepareでSQL文をセットする
				$stmt = $pdo->prepare($sql);
				//bindParamで値をセットする
				$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
				$stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
				$stmt -> bindParam(':datetime', $datetime, PDO::PARAM_STR);
				$stmt -> bindParam(':password', $password, PDO::PARAM_STR);
				$stmt -> bindParam(':id', $id, PDO::PARAM_INT);
				//実行
				$stmt -> execute();		
			}

			//削除機能
			//削除対象番号が入力されている場合
			}elseif (!empty($_POST['deleteNo'])) {
				$id = $_POST['deleteNo'];
				//SQL文　テーブルから削除対象番号が一致するものを取得
				$sql = 'SELECT * FROM keijiban WHERE id=:id';
				//prepareでSQL文をセットする
				$stmt = $pdo->prepare($sql); 
				//bindParamで値をセットする　                 
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 
				//実行
				$stmt->execute();
				//SELECTの結果を全行全列で取得                            
				$results = $stmt->fetchAll(); 
					foreach ($results as $row){	//$rowの中にはテーブルのカラム名が入る
						//パスワードが一致する場合
						if($row['password'] == $_POST['delPass']){
							//SQL文 テーブルから削除対象番号が一致するものを削除
							$sql = 'DELETE from keijiban WHERE id=:id';
							//prepareでSQL文をセットする
							$stmt = $pdo->prepare($sql);
							//bindParamで値をセットする
							$stmt->bindParam(':id', $id, PDO::PARAM_INT);
							//実行
							$stmt->execute();			
						}
					}
					
			//編集機能
			//編集対象番号が入力されている場合
			}elseif (!empty($_POST['editNo'])) {
				$id = $_POST['editNo'];
				//SQL文 keijibanから編集対象番号が一致するものを取得
				$sql = 'SELECT * FROM keijiban WHERE id=:id';
				//prepareでSQL文をセットする
				$stmt = $pdo->prepare($sql); 
				//bindParamで値をセットする　                 
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 
				//実行
				$stmt->execute();
				//SELECTの結果を全行全列で取得                            
				$results = $stmt->fetchAll(); 
					foreach ($results as $row){	//$rowの中にはテーブルのカラム名が入る
						//パスワードが一致する場合
						if($row['password'] == $_POST['ediPass']){
							//編集前の入力値を変数に格納(投稿欄に編集前の内容が現れるようにする)
							$editNo = $_POST['editNo'];
							$preName = $row['name'];
							$preComment = $row['comment'];
							$prePassword = $row['password'];
						}
					}
			}
			
			//表示機能
			$sql = 'SELECT * FROM keijiban';
			$stmt = $pdo->query($sql);
			$results = $stmt->fetchAll();
				foreach ($results as $row){
					//$rowの中にはテーブルのカラム名が入る
					echo $row['id'].' '.$row['name'].' '.$row['datetime'].'<br>'.$row['comment'].'<br>'.'<hr>';
				}

		//エラー出力	
		}catch(PDOException $e){
			echo 'データベースエラー（PDOエラー）';
				//var_dump($e->getMessage());    //エラーの詳細を調べる場合、コメントアウトを外す
		}
			
	
	?>

	<!--投稿フォーム-->
	<p><b>【投稿フォーム】</b></p>
	<form action="" method="post">
		<p>お名前　: <input type="text" name="name" value="<?php echo $preName; ?>" required></p> 
		<p>コメント: <input type="text" name="comment" value="<?php echo $preComment; ?>"required></p>
		<p><input type="hidden" name="editPostNo" value="<?php echo $editNo; ?>"></p>
		<p>パスワード: <input type="password" name="pass" value="<?php echo $prePassword; ?>"required></p>
		<input type="submit" name="submit">
	</form>  
		
	<!--削除フォーム-->
	<p><b>【削除フォーム】</b></p>
	<form action="" method="post">
		<p>投稿番号: <input type="number" name="deleteNo"  required></p>
		<p>パスワード: <input type="password" name="delPass" required></p>
		<input type="submit" name="delete" value="削除">
	</form>
		
	<!--編集フォーム-->
	<p><b>【編集フォーム】</b></p>
	<form action="" method="post">       
		<p>投稿番号: <input type="number" name="editNo"  required></p>
		<p>パスワード: <input type="password" name="ediPass" required></p>
		<input type="submit" name="edit" value="編集">
	</form>

	
</body>
</html>
