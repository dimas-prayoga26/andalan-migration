<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applicant_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('applicant_id')->constrained('applicants', 'id')->cascadeOnDelete();
            $table->foreignUuid('applicant_upload_request_id')->nullable()->constrained('applicant_upload_requests', 'id')->nullOnDelete();
            $table->string('document_type', 40)->index();
            $table->text('file_path');
            $table->text('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamp('uploaded_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['applicant_id', 'document_type']);
            $table->unique('applicant_upload_request_id');
        });

        $this->backfillApplicantDocuments();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_documents');
    }

    private function backfillApplicantDocuments(): void
    {
        DB::table('applicants')
            ->select(['id', 'cv', 'photo', 'created_at', 'updated_at'])
            ->where(static function ($query): void {
                $query
                    ->whereNotNull('cv')
                    ->orWhereNotNull('photo');
            })
            ->orderBy('id')
            ->chunkById(500, function ($applicants): void {
                $documents = [];

                foreach ($applicants as $applicant) {
                    $createdAt = $applicant->created_at ?? now();
                    $updatedAt = $applicant->updated_at ?? $createdAt;

                    foreach ([
                        'cv' => $applicant->cv,
                        'photo_profile' => $applicant->photo,
                    ] as $documentType => $filePath) {
                        $filePath = trim((string) $filePath);

                        if ($filePath === '') {
                            continue;
                        }

                        $documents[] = [
                            'id' => (string) Str::uuid(),
                            'applicant_id' => $applicant->id,
                            'applicant_upload_request_id' => null,
                            'document_type' => $documentType,
                            'file_path' => $filePath,
                            'original_name' => basename(str_replace('\\', '/', $filePath)),
                            'mime_type' => null,
                            'file_size' => null,
                            'uploaded_at' => $createdAt,
                            'created_at' => $createdAt,
                            'updated_at' => $updatedAt,
                        ];
                    }
                }

                if ($documents !== []) {
                    DB::table('applicant_documents')->insert($documents);
                }
            });
    }
};
