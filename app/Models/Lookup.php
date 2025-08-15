<?php
class Lookup
{
    public static function getClasses(): array
    {
        $stmt = DB::get()->query("SELECT id, name FROM classes ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSections(): array
    {
        $stmt = DB::get()->query("SELECT id, name FROM sections ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCategories(): array
    {
        $stmt = DB::get()->query("SELECT id, name FROM categories ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getFamilyCategories(): array
    {
        $stmt = DB::get()->query("SELECT id, name FROM family_categories ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
