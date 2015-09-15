<?php
	class Info
	{
		// договір (номер)
		private $number;
		// дата підписання договору
		private $date_sign;
		// id клієнта
		private $id_customer;
		// ім'я клієнта
		private $name_customer;
		// ім'я компанії
		private $company;
		// перелік назв сервісів
		private $title_service;
		// id договору
		private $id_contract;
		// зберігає інформацію для з'єднання з БД
		private $db;
		
		// функція повернення id контракту
		public function GetContract()
		{
			return $this->id_contract;
		}
		
		// присвоєння значення id контракту
		public function SetContract($id_contract)
		{
			$this->id_contract=$id_contract;
		}
		
		// функція з'єднання з базою даних
		public function __construct($host, $dbname, $user, $pass)
		{
			// $this->db = new PDO($sql.":host=".$host.";dbname=".$dbname, $user, $pass);
			$this->db = mysql_connect($host, $user, $pass);
			mysql_select_db($dbname);
		}
		
		// функція зчитування даних
		public function Read()
		{	
			// обираємо необхідну інформацію за договором			
			$contract_customer = mysql_query("SELECT c.id_contract, c.number, c.date_sign, cu.name_customer, cu.id_customer, cu.company FROM obj_contracts c INNER JOIN obj_customers cu ON c.id_customer=cu.id_customer WHERE c.id_contract='{$this->id_contract}'");
			// розбиття на масив з числовими значеннями
			while ($row = mysql_fetch_array($contract_customer))
			{
				$this->number=$row['number'];
				$this->date_sign=$row['date_sign'];
				$this->id_customer=$row['id_customer'];
					
				$this->name_customer=$row['name_customer'];
				$this->company=$row['company'];
			}
			
			// ініціалізація лічильника
			$i=0;
			// проміжний масив для зберігання даних
			$array_service;
			
			// пошук сервісів
			$service = mysql_query("SELECT title_service FROM obj_services WHERE id_contract='{$this->id_contract}'");
			// розбиття на масив з числовими значеннями
			while ($row = mysql_fetch_array($service))
			{
				$array_service[$i]=$row['title_service'];
				$i++;
			}
			
			// перезапис до атрибутів
			$this->title_service=$array_service;
		}
		
		// виведення інформації у картоковому вигляді
		public function Write()
		{	
			// шаблон
			$filename = 'view.html';
			$file = file_get_contents($filename);
			
			// заміна потрібних полів
			$file = str_replace('[name_customer]', $this->name_customer, $file);
			$file = str_replace('[company]', $this->company, $file);
			$file = str_replace('[number]', $this->number, $file);
			$file = str_replace('[date_sign]', $this->date_sign, $file);
			
			// перелік сервісу
			$services_name;
			
			// запис у один рядок
			for ($i=0; $i<count($this->title_service); $i++)
			{
				$services_name=$services_name.$this->title_service[$i]."<br>";
			}
			
			$file = str_replace('[services_name]', $services_name, $file);
			
			// виведення
			echo $file;
		}
	}
	
	
	
	// пошук об'єктів у файлі
	function SearchObject($filename, $id_contract)
	{	
		// знайдений об'єкт
		$search_object=false;
		// рядкове зчитування
		$handle = fopen($filename, "a+");
		// прохід по файлу
		while (!feof($handle))
		{
			// зчитування рядка
			$buffer = fgets($handle);
			// десеріалізація
			$search_object = unserialize($buffer);	
			// перевірка властивостей
			if ($search_object!=NULL && (($search_object->GetContract())==$id_contract))
			break;
		}
		// закриття файлу
		fclose($handle);
		
		return $search_object;
	}
	
	// запис об'єктів до файлу
	function WriteObject($filename, $object)
	{
		// відкриття файлу
		$file = fopen($filename, 'w');
		// серіалізація об'єкту
		$buffer=serialize($object);
		// запис
		fwrite($file, $buffer."\n");
		// закриття файлу
		fclose($file);		
	}
	
	
	
	// головна функція
	function main($id_contract, $filename, $host, $dbname, $user, $pass)
	{
		// вхідні дані
		$id_contract=1;

		// фабрика об'єктів
		$filename='factory.txt';
		
		// пошук об'єкта у фабриці
		$object=SearchObject($filename, $id_contract);
		
		// якщо об'єкт не знайдено
		if ($object==false)
		{
			// створюємо об'єкт класу
			$object = new Info($host, $dbname, $user, $pass);	
			// задаємо id контракту
			$object->SetContract($id_contract);
			// здійснюємо пошук (зчитування)
			$object->Read();
			// здійснюємо виведення
			$object->Write();
			// запис до файлу
			WriteObject($filename, $object);
		}
		else
		{
			// вивести інформацію
			$object->Write();
		}
	}
	
	// параметри: id контракту, назва файлу зі створеними об'єктами
	main(1,'factory.txt','localhost','db','user_db','user_db');
?>