<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id'); // tenant_id as string to match tenants.id
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade'); // Foreign key to the tenants table
        
            $table->string('name'); // File name
            $table->string('path'); // Path to the file (e.g., S3 path)
            $table->integer('size'); // File size in bytes
            $table->string('mime_type'); // MIME type of the file
            
            $table->unsignedBigInteger('uploaded_by'); // uploaded_by as unsignedBigInteger to match users.id
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade'); // Foreign key to the users table
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
