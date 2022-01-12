@extends('layout')
  
@section('content')
<div class="container-fluid mt-5">
      <div class="row justify-content-center">
          <div class="col-md-4">
              <div class="rounded shadow-lg card">
                  <div class="card-header"><h6>Request Reset Password</h6></div>
                  <div class="card-body">
  
                    @if (Session::has('message'))
                         <div class="alert alert-success" role="alert">
                            {{ Session::get('message') }}
                        </div>
                    @endif
  
                      <form action="{{ route('forget.password.post') }}" method="POST">
                          @csrf
                          <div class="form-group row">
                              <label for="email_address" class="col-md-4 col-form-label text-md-right">Alamat E-Mail</label>
                              <div class="col-md-6">
                                  <input type="text" id="email_address" class="form-control" name="email" required autofocus>
                                  @if ($errors->has('email'))
                                      <span class="text-danger">{{ $errors->first('email') }}</span>
                                  @endif
                              </div>
                          </div> <br>
                          <div align="center">
                              <button type="submit" class="btn btn-success">
                                  Kirim Link Ubah Password
                              </button>
                          </div>
                          <br>
                      </form>
                        
                  </div>
              </div>
          </div>
      </div>
  </div>
@endsection