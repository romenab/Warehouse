<?php
require_once 'vendor/autoload.php';

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class Tasks
{
    private string $userOnline;
    private array $allProducts;
    private array $changesMade;
    public ?Carbon $createdAt;
    public ?Carbon $updatedAt;


    public function __construct(array $allProducts = [], array $changesMade = [], ?Carbon $createdAt = null, ?Carbon $updatedAt = null)
    {
        $this->allProducts = $allProducts;
        $this->changesMade = $changesMade;
        $this->createdAt = $createdAt ? Carbon::parse($createdAt) : Carbon::now();
        $this->updatedAt = $updatedAt ? Carbon::parse($updatedAt) : Carbon::now();
    }

    public function logIn(): void
    {
        $attempts = 3;
        $allUsers = json_decode(file_get_contents("users.json"), true);

        while ($attempts > 0) {
            $username = readline("Username: ");
            $password = readline("Password: ");

            foreach ($allUsers["users"] as $user) {
                if ($user["username"] === $username && $user["password"] === $password) {
                    echo "Login was successful!" . PHP_EOL;
                    $this->userOnline = $user["username"];
                    $this->load("products.json", "logChanges.json");
                    return;
                }
            }
            echo "Login was not successful! Try again!" . PHP_EOL;
            $attempts--;
        }
        exit("Maximum login attempts reached! Bye!");
    }


    public function add(): void
    {
        while (true) {
            $userProduct = readline("Enter your product name: ");
            if ($userProduct == "") {
                echo "Invalid input!" . PHP_EOL;
                continue;
            }
            $userUnits = (int)readline("Enter your unit quantity: ");
            if ($userUnits < 0) {
                echo "Invalid input!" . PHP_EOL;
                continue;
            }
            $uuid = Uuid::uuid4()->toString();
            $this->allProducts[] = [
                "id" => $uuid,
                "name" => $userProduct,
                "createdAt" => Carbon::now()->toDateTimeString(),
                "updatedAt" => $this->updatedAt->toDateTimeString(),
                "units" => $userUnits
            ];
            echo "Product added successfully!" . PHP_EOL;
            $this->changesLog("Added", $uuid, $userProduct, $userUnits);
            break;
        }
    }

    public function update(): void
    {
        while (true) {
            $userId = (int)readline("Enter product ID: ");
            $userUpdate = readline("Enter your unit quantity: ");
            if (!is_numeric($userUpdate) && $userUpdate >= 0) {
                echo "Invalid input! Please enter a valid number." . PHP_EOL;
                continue;
            }
            foreach ($this->allProducts as &$product) {
                if ($product["id"] == $userId) {
                    $product["units"] = $userUpdate;
                    $product["updatedAt"] = Carbon::now()->toDateTimeString();
                    echo "Product updated successfully!" . PHP_EOL;
                    $this->changesLog("Updated", $product["id"], $product["name"], $userUpdate);
                    break;
                }
            }
            break;
        }
    }

    public function delete(): void
    {
        $userId = (int)readline("Enter product ID: ");
        foreach ($this->allProducts as $key => $product) {
            if ($product["id"] == $userId) {
                unset($this->allProducts[$key]);
                echo "Product deleted successfully!" . PHP_EOL;
                $this->changesLog("Deleted", $product["id"], $product["name"], $product["units"]);
                break;
            }
        }
    }

    public function changesLog(string $action, string $id, string $name, int $units): void
    {
        $this->changesMade[] = [
            "username" => $this->userOnline,
            "action" => $action,
            "id" => $id,
            "name" => $name,
            "units" => $units,
            "updatedAt" => Carbon::now()->toDateTimeString()
        ];
    }
    public function save(string $productJsonFile, string $changesJsonFile): void
    {
        file_put_contents($productJsonFile, json_encode($this->allProducts, JSON_PRETTY_PRINT));
        file_put_contents($changesJsonFile, json_encode($this->changesMade, JSON_PRETTY_PRINT));
    }

    public function load(string $productsJsonFile, string $changesJsonFile): void
    {
        $this->allProducts = json_decode(file_get_contents($productsJsonFile), true);
        $this->changesMade = json_decode(file_get_contents($changesJsonFile), true);
    }

    public function getAllProducts(): array
    {
        return $this->allProducts;
    }

    public function getChangesMade(): array
    {
        return $this->changesMade;
    }

}
