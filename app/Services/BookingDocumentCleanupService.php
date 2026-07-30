<?php

namespace App\Services;

use App\Repositories\Interfaces\BookingDocumentRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class BookingDocumentCleanupService
{
    private const DISK = 'documents';

    private const PREFIX = 'bookings';

    private const BATCH_SIZE = 1000;

    public function __construct(
        private readonly BookingDocumentRepositoryInterface $documents,
    ) {}

    public function cleanup(int $retentionDays): void
    {
        $this->deleteExpiredSoftDeletedDocuments($retentionDays);
        $this->deleteOrphanedDocumentObjects();

        Log::info('S3 booking document cleanup completed.', [
            'retention_days' => $retentionDays,
        ]);
    }

    private function deleteExpiredSoftDeletedDocuments(int $retentionDays): void
    {
        $this->documents
            ->expiredDeletedDocuments(
                self::DISK,
                now()->subDays($retentionDays),
            )
            ->chunk(self::BATCH_SIZE)
            ->each(fn (LazyCollection $documents) => $this->deleteDocumentBatch($documents));
    }

    private function deleteDocumentBatch(LazyCollection $documents): void
    {
        Storage::disk(self::DISK)->delete(
            $documents->pluck('key')->all(),
        );

        $this->documents->forceDeleteTrashedByIds(
            $documents->pluck('id')->all(),
        );
    }

    private function deleteOrphanedDocumentObjects(): void
    {
        collect(Storage::disk(self::DISK)->allFiles(self::PREFIX))
            ->chunk(self::BATCH_SIZE)
            ->each(fn (Collection $keys) => $this->deleteOrphanedBatch($keys));
    }

    private function deleteOrphanedBatch(Collection $keys): void
    {
        $orphanedKeys = $this->documents->orphanedKeys(
            self::DISK,
            $keys->all(),
        );

        if ($orphanedKeys->isEmpty()) {
            return;
        }

        Storage::disk(self::DISK)->delete(
            $orphanedKeys->all(),
        );
    }
}
