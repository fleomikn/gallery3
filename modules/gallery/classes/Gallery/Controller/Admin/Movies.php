<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class Gallery_Controller_Admin_Movies extends Controller_Admin {
  public function action_index() {
    // Print screen from new form.
    $form = $this->_get_admin_form();
    $this->_print_view($form);
  }

  public function action_save() {
    Access::verify_csrf();
    $form = $this->_get_admin_form();
    if ($form->validate()) {
      Module::set_var("gallery", "movie_allow_uploads", $form->settings->allow_uploads->value);
      if ($form->settings->rebuild_thumbs->value) {
        Graphics::mark_dirty(true, false, "movie");
      }
      // All done - redirect with message.
      Message::success(t("Movies settings updated successfully"));
      $this->redirect("admin/movies");
    }
    // Something went wrong - print view from existing form.
    $this->_print_view($form);
  }

  protected function _print_view($form) {
    list ($ffmpeg_version, $ffmpeg_date) = Movie::get_ffmpeg_version();
    $ffmpeg_version = $ffmpeg_date ? "{$ffmpeg_version} ({$ffmpeg_date})" : $ffmpeg_version;
    $ffmpeg_path = Movie::find_ffmpeg();
    $ffmpeg_dir = substr($ffmpeg_path, 0, strrpos($ffmpeg_path, "/"));

    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Movies settings");
    $view->content = new View("admin/movies.html");
    $view->content->form = $form;
    $view->content->ffmpeg_dir = $ffmpeg_dir;
    $view->content->ffmpeg_version = $ffmpeg_version;
    $this->response->body($view);
  }

  protected function _get_admin_form() {
    $form = new Forge("admin/movies/save", "", "post", array("id" => "g-movies-admin-form"));
    $group = $form->group("settings")->label(t("Settings"));
    $group->dropdown("allow_uploads")
      ->label(t("Allow movie uploads into Gallery (does not affect existing movies)"))
      ->options(array("autodetect"=>t("only if FFmpeg is detected (default)"),
                      "always"=>t("always"), "never"=>t("never")))
      ->selected(Module::get_var("gallery", "movie_allow_uploads", "autodetect"));
    $group->checkbox("rebuild_thumbs")
      ->label(t("Rebuild all movie thumbnails (once FFmpeg is installed, use this to update existing movie thumbnails)"))
      ->checked(false);  // always set as false
    $form->submit("save")->value(t("Save"));
    return $form;
  }
}