@extends('layouts.app')

@section('title', 'Journal Entries')

@section('content')

<section class="content-header">

    <div class="container-fluid">

        <div class="row">

            <div class="col-sm-6">

                <h1>Journal Entries</h1>

            </div>

            <div class="col-sm-6">

                <ol class="breadcrumb float-sm-right">

                    <li class="breadcrumb-item">
                        Accounting
                    </li>

                    <li class="breadcrumb-item active">
                        Journal Entries
                    </li>

                </ol>

            </div>

        </div>

    </div>

</section>


<section class="content">

<div class="container-fluid">

    @include('partials.alerts')


    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Journal Entry List
            </h3>

            <div class="card-tools">

              

                    <a href="{{ route('accounting.journal-entries.create') }}"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-plus"></i>
                        New Journal Entry

                    </a>

               

            </div>

        </div>


        <div class="card-body table-responsive p-0">

            <table class="table table-bordered table-hover">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Branch</th>
                        <th>Rows</th>
                        <th>Created</th>
                        <th width="150">Actions</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($journalEntries as $entry)

                        <tr>

                            <td>
                                {{ $entry->id }}
                            </td>

                            <td>
                                {{ $entry->journal_date }}
                            </td>

                            <td>
                                {{ $entry->reference_no ?? '-' }}
                            </td>

                            <td>
                                {{ $entry->branch->name ?? '-' }}
                            </td>

                            <td>
                                {{ $entry->details_count }}
                            </td>

                            <td>
                                {{ $entry->created_at?->format('Y-m-d H:i') }}
                            </td>

                            <td>

                              

                                    <a href="{{ route('accounting.journal-entries.edit', $entry) }}"
                                       class="btn btn-sm btn-primary">

                                        <i class="fas fa-edit"></i>

                                    </a>

                             


                                @can('journal_entry.delete')

                                    <form
                                        action="{{ route('accounting.journal-entries.destroy', $entry) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete this journal entry?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-danger">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                @endcan

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
                                class="text-center">

                                No journal entries found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="card-footer">

            {{ $journalEntries->links() }}

        </div>

    </div>

</div>

</section>

@endsection