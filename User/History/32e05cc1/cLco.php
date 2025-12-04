<?php

class Supplier {
    private $conn;
    private $table_name = "suppliers";

    public $id;
    public $name;
    public $cif_nif;
    public $tax_id;
    public $email;
    public $phone;
    public $address;
    public $postal_code;
    public $city;
    public $province;
    public $country;
    public $website;
    public $logo_path;
    public $tipo_proveedor;
    public $categoria;
    public $estado;
    public $payment_terms;
    public $default_payment_method;
    public $iban;
    public $swift;
    public $bank_name;
    public $default_discount;
    public $credit_limit;
    public $contact_person;
    public $contact_email;
    public $contact_phone;
    public $rating;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name = :name,
                    cif_nif = :cif_nif,
                    tax_id = :tax_id,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    postal_code = :postal_code,
                    city = :city,
                    province = :province,
                    country = :country,
                    website = :website,
                    logo_path = :logo_path,
                    tipo_proveedor = :tipo_proveedor,
                    categoria = :categoria,
                    estado = :estado,
                    payment_terms = :payment_terms,
                    default_payment_method = :default_payment_method,
                    iban = :iban,
                    swift = :swift,
                    bank_name = :bank_name,
                    default_discount = :default_discount,
                    credit_limit = :credit_limit,
                    contact_person = :contact_person,
                    contact_email = :contact_email,
                    contact_phone = :contact_phone,
                    rating = :rating,
                    notes = :notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->cif_nif = htmlspecialchars(strip_tags($this->cif_nif ?? ''));
        $this->tax_id = htmlspecialchars(strip_tags($this->tax_id ?? ''));
        $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));
        $this->postal_code = htmlspecialchars(strip_tags($this->postal_code ?? ''));
        $this->city = htmlspecialchars(strip_tags($this->city ?? ''));
        $this->province = htmlspecialchars(strip_tags($this->province ?? ''));
        $this->country = htmlspecialchars(strip_tags($this->country ?? 'España'));
        $this->website = htmlspecialchars(strip_tags($this->website ?? ''));
        $this->logo_path = htmlspecialchars(strip_tags($this->logo_path ?? ''));
        $this->tipo_proveedor = htmlspecialchars(strip_tags($this->tipo_proveedor ?? 'servicios'));
        $this->categoria = htmlspecialchars(strip_tags($this->categoria ?? ''));
        $this->estado = htmlspecialchars(strip_tags($this->estado ?? 'activo'));
        $this->payment_terms = intval($this->payment_terms ?? 30);
        $this->default_payment_method = htmlspecialchars(strip_tags($this->default_payment_method ?? 'transfer'));
        $this->iban = htmlspecialchars(strip_tags($this->iban ?? ''));
        $this->swift = htmlspecialchars(strip_tags($this->swift ?? ''));
        $this->bank_name = htmlspecialchars(strip_tags($this->bank_name ?? ''));
        $this->default_discount = floatval($this->default_discount ?? 0.00);
        $this->credit_limit = !empty($this->credit_limit) ? floatval($this->credit_limit) : null;
        $this->contact_person = htmlspecialchars(strip_tags($this->contact_person ?? ''));
        $this->contact_email = htmlspecialchars(strip_tags($this->contact_email ?? ''));
        $this->contact_phone = htmlspecialchars(strip_tags($this->contact_phone ?? ''));
        $this->rating = !empty($this->rating) ? intval($this->rating) : null;
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":cif_nif", $this->cif_nif);
        $stmt->bindParam(":tax_id", $this->tax_id);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":postal_code", $this->postal_code);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":province", $this->province);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":website", $this->website);
        $stmt->bindParam(":logo_path", $this->logo_path);
        $stmt->bindParam(":tipo_proveedor", $this->tipo_proveedor);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":payment_terms", $this->payment_terms);
        $stmt->bindParam(":default_payment_method", $this->default_payment_method);
        $stmt->bindParam(":iban", $this->iban);
        $stmt->bindParam(":swift", $this->swift);
        $stmt->bindParam(":bank_name", $this->bank_name);
        $stmt->bindParam(":default_discount", $this->default_discount);
        $stmt->bindParam(":credit_limit", $this->credit_limit);
        $stmt->bindParam(":contact_person", $this->contact_person);
        $stmt->bindParam(":contact_email", $this->contact_email);
        $stmt->bindParam(":contact_phone", $this->contact_phone);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->name = $row['name'];
            $this->cif_nif = $row['cif_nif'];
            $this->tax_id = $row['tax_id'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->postal_code = $row['postal_code'];
            $this->city = $row['city'];
            $this->province = $row['province'];
            $this->country = $row['country'];
            $this->website = $row['website'];
            $this->logo_path = $row['logo_path'];
            $this->tipo_proveedor = $row['tipo_proveedor'];
            $this->categoria = $row['categoria'];
            $this->estado = $row['estado'];
            $this->payment_terms = $row['payment_terms'];
            $this->default_payment_method = $row['default_payment_method'];
            $this->iban = $row['iban'];
            $this->swift = $row['swift'];
            $this->bank_name = $row['bank_name'];
            $this->default_discount = $row['default_discount'];
            $this->credit_limit = $row['credit_limit'];
            $this->contact_person = $row['contact_person'];
            $this->contact_email = $row['contact_email'];
            $this->contact_phone = $row['contact_phone'];
            $this->rating = $row['rating'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    name = :name,
                    cif_nif = :cif_nif,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    website = :website,
                    logo_path = :logo_path,
                    notes = :notes
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->cif_nif = htmlspecialchars(strip_tags($this->cif_nif));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->website = htmlspecialchars(strip_tags($this->website));
        $this->logo_path = htmlspecialchars(strip_tags($this->logo_path));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":cif_nif", $this->cif_nif);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":website", $this->website);
        $stmt->bindParam(":logo_path", $this->logo_path);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
