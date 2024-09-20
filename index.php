<?php
session_start();

if (!isset($_SESSION["todos"])) {
	$_SESSION["todos"] = [
		[
			"task" => "Example Task",
			"urgency" => "high",
			"isCompleted" => 0,
			"id" => uniqid(),
		]
	];
}

$tasks = $_SESSION["todos"];
$task = [];
$isEdit = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$tempAction = $_POST["action"] ?? "create";
	$tempUrgency = $_POST["urgency"] ?? "";
	$id = $_POST["id"] ?? "";
	$tempTask;

	if (isset($_POST["task"])) {
		$tempTask = $_POST["task"];
	} else {
		$filteredItems = array_filter($tasks, function ($task) use ($id) {
			return $task["id"] === $id;
		});
		$tempTask = reset($filteredItems);
	}


	switch ($tempAction) {
		case "create":
			$tempId = uniqid();
			$newTask = [
				"task" => $tempTask,
				"urgency" => $tempUrgency,
				"id" => $tempId,
				"isCompleted" => 0,
			];
			array_push($tasks, $newTask);
			$_SESSION["todos"] = $tasks;
			break;
		case "edit":
			$task = $tempTask;
			break;
		case "check":
			foreach ($tasks as $key => $task) {
				if ($task["id"] !== $id) {
					$tasks[$key] = $task;
				} else {
					$tasks[$key] = [
						"task" => $task["task"],
						"urgency" => $task["urgency"],
						"id" => $task["id"],
						"isCompleted" => $task["isCompleted"] === 1 ? 0 : 1,
					];
				}
			}
			$_SESSION["todos"] = $tasks;
			$task = [];
			break;
		case "delete":
			$tasks = array_filter($tasks, function ($task) use ($id) {
				if ($task["id"] !== $id) {
					return $task;
				}
			});
			$_SESSION["todos"] = $tasks;
			$task = [];
			break;
		case "go-update":
			foreach ($tasks as $key => $value) {
				if ($value["id"] === $id) {
					$readyTask = [
						"task" => $tempTask,
						"id" => $id,
						"urgency" => $tempUrgency,
						"isCompleted" => 0,
					];
					$tasks[$key] = $readyTask;
				}
			}
			$_SESSION["todos"] = $tasks;
			break;
	}
	$_POST = array();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<script src="https://cdn.tailwindcss.com"></script>
	<title>Todo PHP</title>
</head>

<body class="bg-gray-100 container mx-auto max-w-3xl px-5 pt-10">
	<form method="POST" action="/" class="flex items-start gap-2">
		<?= isset($task["task"]) && $task["id"] ? '<input
			name="id"
			placeholder="Enter a task"
			class="hidden"
			value="' . $task["id"] . '"
			/>' : ""
		?>
		<input
			name="task"
			placeholder="Enter a task"
			class="flex-1 p-2 rounded border-[1px] shadow-md text-sm"
			<?= isset($task["task"]) && $task["task"] ? "value='" . $task["task"] . "'" : "" ?> />
		<select name="urgency" class="p-2 rounded text-sm border-[1px] shadow-md">
			<option value="low" <?= isset($task["urgency"]) && $task["urgency"] === "low" ? "selected" : "" ?>>Low</option>
			<option value="medium" <?= isset($task["urgency"]) && $task["urgency"] === "medium" ? "selected" : "" ?>>Medium</option>
			<option value="high" <?= isset($task["urgency"]) && $task["urgency"] === "high" ? "selected" : "" ?>>High</option>
		</select>
		<button
			type="submit"
			class="p-2 rounded bg-blue-500 text-sm border-[1px] shadow-md text-white"
			name="action"
			value=<?= isset($task["task"]) ? "go-update" : "create" ?>>
			<?= isset($task["task"]) ? "Update" : "Create" ?>
		</button>
	</form>
	<div class="flex flex-col gap-4 mt-5 px-5">
		<?php foreach ($tasks as $task) : ?>
			<form
				method="POST"
				action="/"
				class="w-full <?= $task["urgency"] === "high" ? "bg-red-500" : ($task["urgency"] === "medium" ? "bg-yellow-500" : "bg-gray-200") ?> p-2 rounded flex items-start gap-4 mb-0">
				<input
					class="hidden"
					value="<?= $task["id"] ?>"
					name="id" />
				<input
					class="hidden"
					value="<?= $task["urgency"] ?>"
					name="urgency" />
				<button type="submit" name="action" value="check" class="w-6 h-6 flex items-center justify-center bg-white rounded hover:bg-gray-100 transition-all duration-300 border-[1px]">
					<?= $task["isCompleted"] ? '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-5">
							<g id="SVGRepo_bgCarrier" stroke-width="0"></g>
							<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
							<g id="SVGRepo_iconCarrier">
								<path d="M19 5L4.99998 19M5.00001 5L19 19" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
							</g>
						</svg>' : '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-5">
							<g id="SVGRepo_bgCarrier" stroke-width="0"></g>
							<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
							<g id="SVGRepo_iconCarrier">
								<path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
							</g>
						</svg>' ?>
				</button>
				<div class="<?= $task["isCompleted"] ? "line-through" : "" ?> flex-1">
					<?= $task["task"] ?>
				</div>
				<div class="flex items-center gap-2">
					<button type="submit" name="action" value="edit" class="stroke-blue-500 !fill-transparent hover:stroke-white hover:bg-blue-500 w-6 h-6 flex items-center justify-center bg-white rounded transition-all duration-300 border-[1px]" <?= $task["isCompleted"] == 1 ? "disabled" : "" ?>>
						<svg
							viewBox="0 0 24 24"
							fill="none"
							xmlns="http://www.w3.org/2000/svg"
							class="size-5">
							<g id="SVGRepo_bgCarrier" stroke-width="0"></g>
							<g
								id="SVGRepo_tracerCarrier"
								stroke-linecap="round"
								stroke-linejoin="round"></g>
							<g id="SVGRepo_iconCarrier">
								<path
									fill-rule="evenodd"
									clip-rule="evenodd"
									d="M8.56078 20.2501L20.5608 8.25011L15.7501 3.43945L3.75012 15.4395V20.2501H8.56078ZM15.7501 5.56077L18.4395 8.25011L16.5001 10.1895L13.8108 7.50013L15.7501 5.56077ZM12.7501 8.56079L15.4395 11.2501L7.93946 18.7501H5.25012L5.25012 16.0608L12.7501 8.56079Z"
									fill="#000000"></path>
							</g>
						</svg>
					</button>
					<button type="submit" name="action" value="delete" class="stroke-red-500 !fill-transparent hover:stroke-white hover:bg-red-500 w-6 h-6 flex items-center justify-center bg-white rounded transition-all duration-300 border-[1px]">
						<svg
							viewBox="0 0 24 24"
							fill="none"
							xmlns="http://www.w3.org/2000/svg"
							class="size-5">
							<g id="SVGRepo_bgCarrier" stroke-width="0"></g>
							<g
								id="SVGRepo_tracerCarrier"
								stroke-linecap="round"
								stroke-linejoin="round"></g>
							<g id="SVGRepo_iconCarrier">
								<path
									d="M12.0004 9.5L17.0004 14.5M17.0004 9.5L12.0004 14.5M4.50823 13.9546L7.43966 17.7546C7.79218 18.2115 7.96843 18.44 8.18975 18.6047C8.38579 18.7505 8.6069 18.8592 8.84212 18.9253C9.10766 19 9.39623 19 9.97336 19H17.8004C18.9205 19 19.4806 19 19.9084 18.782C20.2847 18.5903 20.5907 18.2843 20.7824 17.908C21.0004 17.4802 21.0004 16.9201 21.0004 15.8V8.2C21.0004 7.0799 21.0004 6.51984 20.7824 6.09202C20.5907 5.71569 20.2847 5.40973 19.9084 5.21799C19.4806 5 18.9205 5 17.8004 5H9.97336C9.39623 5 9.10766 5 8.84212 5.07467C8.6069 5.14081 8.38579 5.2495 8.18975 5.39534C7.96843 5.55998 7.79218 5.78846 7.43966 6.24543L4.50823 10.0454C3.96863 10.7449 3.69883 11.0947 3.59505 11.4804C3.50347 11.8207 3.50347 12.1793 3.59505 12.5196C3.69883 12.9053 3.96863 13.2551 4.50823 13.9546Z"
									stroke-width="2"
									stroke-linecap="round"
									stroke-linejoin="round"></path>
							</g>
						</svg>
					</button>
				</div>
			</form>
		<?php endforeach; ?>
	</div>
</body>

</html>